<?php

namespace App\Controller;

use App\Repository\PlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Stripe\ApiRequestor;
use App\Service\SymfonyStripeHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[IsGranted('ROLE_USER')]
class SubscriptionController extends AbstractController
{
    public function change(PlanRepository $planRepository): Response
    {
        $user = $this->getUser();
        $currentPlan = $user->getPlan();
        $plans = $planRepository->findBy(['active' => true]);

        return $this->render('subscription/change.html.twig', [
            'current_plan' => $currentPlan,
            'plans' => $plans,
        ]);
    }

    public function subscribe(int $planId, PlanRepository $planRepository, EntityManagerInterface $em, HttpClientInterface $httpClient): Response
    {
        $plan = $planRepository->find($planId);

        if (!$plan) {
            $this->addFlash('error', 'Plan invalide.');
            return $this->redirectToRoute('app_subscription_change');
        }

        if ($plan->getName() === 'FREE') {
            $user = $this->getUser();
            $user->setPlan($plan);
            $user->setRoles(['ROLE_USER']);
            $em->flush();

            $this->addFlash('success', 'Vous êtes maintenant sur le plan FREE.');
            return $this->redirectToRoute('app_home');
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        ApiRequestor::setHttpClient(new SymfonyStripeHttpClient($httpClient));

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Plan ' . $plan->getName(),
                            'description' => $plan->getDescription(),
                        ],
                        'unit_amount' => $plan->getPrice() * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('app_subscription_success', ['planId' => $planId], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('app_subscription_change', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'metadata' => [
                    'user_id' => $this->getUser()->getId(),
                    'plan_id' => $planId,
                ],
            ]);

            return $this->redirect($session->url);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            return $this->redirectToRoute('app_subscription_change');
        }
    }

    public function success(int $planId, PlanRepository $planRepository, EntityManagerInterface $em): Response
    {
        $plan = $planRepository->find($planId);
        $user = $this->getUser();

        if ($user && $plan) {
            $user->setPlan($plan);

            if ($plan->getName() === 'PREMIUM') {
                $user->setRoles(['ROLE_PREMIUM']);
            } elseif ($plan->getName() === 'BASIC') {
                $user->setRoles(['ROLE_BASIC']);
            }

            $em->flush();
            $this->addFlash('success', 'Paiement réussi ! Vous êtes maintenant sur le plan ' . $plan->getName() . ' !');
        }

        return $this->render('payment/success.html.twig', [
            'plan' => $plan,
        ]);
    }

    public function webhook(Request $request): Response
    {
        return new Response('OK', 200);
    }

    public function cancel(): Response
    {
        return $this->render('payment/cancel.html.twig');
    }
}