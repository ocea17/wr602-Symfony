<?php

namespace App\Service;

use App\Entity\Plan;
use App\Entity\User;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\ApiRequestor;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StripeService
{
    public function __construct(
        private string $secretKey,
        private string $webhookSecret,
        HttpClientInterface $httpClient,
    ) {
        Stripe::setApiKey($this->secretKey);

        // Force l'utilisation du HttpClient Symfony
        ApiRequestor::setHttpClient(new SymfonyStripeHttpClient($httpClient));
    }

    public function createCheckoutSession(
        User $user,
        Plan $plan,
        string $successUrl,
        string $cancelUrl,
    ): string {
        $session = Session::create([
            'mode' => 'subscription',
            'customer_email' => $user->getEmail(),
            'line_items' => [[
                'price' => $plan->getStripePriceId(),
                'quantity' => 1,
            ]],
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'user_id' => $user->getId(),
                'plan_id' => $plan->getId(),
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => $user->getId(),
                    'plan_id' => $plan->getId(),
                ],
            ],
        ]);

        return $session->url;
    }

    public function constructWebhookEvent(string $payload, string $sigHeader): \Stripe\Event
    {
        return Webhook::constructEvent($payload, $sigHeader, $this->webhookSecret);
    }
}