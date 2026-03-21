<?php

namespace App\Controller;

use App\Entity\Plan;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class PaymentController extends AbstractController
{
    /**
     * Crée Session Stripe et redirige l'utilisateur vers Stripe.
     */
    public function checkout(
        Plan $plan,
        StripeService $stripeService,
    ): Response
    {

        if ($plan->getStripePriceId() === null) {
            $this->addFlash('info', 'Ce plan est gratuit, aucun paiement requis.');
            return $this->redirectToRoute('app_home');
        }

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
            $this->getUser(),
            $plan,
            $successUrl,
            $cancelUrl,
        );

        return $this->redirect($checkoutUrl);
    }

    /**
     * Page affichée après un paiement réussi.
     */
    public function success(): Response
    {
        return $this->render('payment/success.html.twig');
    }

    /**
     * Page affichée si l'utilisateur annule le paiement.
     */
    public function cancel(): Response
    {
        return $this->render('payment/cancel.html.twig');
    }
}