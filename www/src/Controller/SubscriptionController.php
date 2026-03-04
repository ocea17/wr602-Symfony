<?php

namespace App\Controller;

use App\Repository\PlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class SubscriptionController extends AbstractController
{
    public function change(
        Request $request,
        PlanRepository $planRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $currentPlan = $user->getPlan();

        // Récupère tous les plans disponibles
        $plans = $planRepository->findBy(['active' => true]);

        if ($request->isMethod('POST')) {
            $planId = $request->request->get('plan_id');
            $newPlan = $planRepository->find($planId);

            if (!$newPlan) {
                $this->addFlash('error', 'Plan invalide.');
                return $this->redirectToRoute('app_subscription_change');
            }

            $user->setPlan($newPlan);

            if ($newPlan->getName() === 'PREMIUM') {
                $user->setRoles(['ROLE_PREMIUM']);
            } elseif ($newPlan->getName() === 'BASIC') {
                $user->setRoles(['ROLE_BASIC']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }

            $em->flush();

            $this->addFlash('success', 'Votre abonnement a été changé avec succès vers le plan ' . $newPlan->getName() . ' !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('subscription/change.html.twig', [
            'current_plan' => $currentPlan,
            'plans' => $plans,
        ]);
    }
}