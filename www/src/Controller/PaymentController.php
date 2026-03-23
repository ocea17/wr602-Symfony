<?php

namespace App\Controller;

use App\Entity\Plan;
use App\Entity\User;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class PaymentController extends AbstractController
{
    public function checkout(
        Plan $plan,
        StripeService $stripeService,
    ): Response {
        if ($plan->getStripePriceId() === null) {
            $this->addFlash('info', 'Ce plan est gratuit, aucun paiement requis.');
            return $this->redirectToRoute('app_home');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $successUrl = $this->generateUrl(
            'app_payment_success',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $cancelUrl = $this->generateUrl(
            'app_payment_cancel',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $checkoutUrl = $stripeService->createCheckoutSession(
            $user,
            $plan,
            $successUrl,
            $cancelUrl,
        );

        return $this->redirect($checkoutUrl);
    }

    public function success(EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $em->refresh($user);

        return $this->render('payment/success.html.twig');
    }

    public function cancel(): Response
    {
        return $this->render('payment/cancel.html.twig');
    }
}