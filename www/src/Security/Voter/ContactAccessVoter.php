<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ContactAccessVoter extends Voter
{
    public const VIEW = 'CONTACT_VIEW';
    public const EDIT = 'CONTACT_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (! $user instanceof User) {
            return false;
        }

        $plan = $user->getPlan();
        $planName = $plan->getName();

        // FREE n'a pas accès aux contacts
        if ('FREE' === $planName) {
            return false;
        }

        // BASIC et PREMIUM ont accès
        return in_array($planName, ['BASIC', 'PREMIUM']);
    }
}
