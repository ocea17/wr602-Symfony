<?php

namespace App\Service;

use App\Entity\Plan;
use App\Entity\User;
use Stripe\ApiRequestor;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StripeService
{
    public function __construct(
        private string $secretKey,
        private string $webhookSecret,
        HttpClientInterface $httpClient,
    ) {
        Stripe::setApiKey($this->secretKey);
        ApiRequestor::setHttpClient(new SymfonyStripeHttpClient($httpClient));
    }

    public function createCheckoutSession(
        User $user,
        Plan $plan,
        string $successUrl,
        string $cancelUrl,
    ): string {
        $customerId = $user->getStripeCustomerId();

        if (! $customerId) {
            $customer = Customer::create([
                'email' => $user->getEmail(),
                'metadata' => [
                    'user_id' => $user->getId(),
                ],
            ]);
            $customerId = $customer->id;
        }

        $session = Session::create([
            'mode' => 'subscription',
            'customer' => $customerId,
            'line_items' => [[
                'price' => $plan->getStripePriceId(),
                'quantity' => 1,
            ]],
            'success_url' => $successUrl.'?session_id={CHECKOUT_SESSION_ID}',
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
