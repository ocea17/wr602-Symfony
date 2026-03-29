<?php

namespace App\Security\Voter;

use App\Entity\Tool;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ToolAccessVoter extends Voter
{
    public const ACCESS = 'TOOL_ACCESS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::ACCESS === $attribute && $subject instanceof Tool;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (! $user instanceof User) {
            return false;
        }

        /** @var Tool $tool */
        $tool = $subject;

        $userPlan = $user->getPlan();
        if (null === $userPlan) {
            return false;
        }

        return $userPlan->getTools()->contains($tool);
    }
}
