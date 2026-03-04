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
        return $attribute === self::ACCESS && $subject instanceof Tool;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // L'utilisateur doit être connecté
        if (!$user instanceof User) {
            return false;
        }

        /** @var Tool $tool */
        $tool = $subject;

        // Récupère le plan de l'utilisateur
        $userPlan = $user->getPlan();
        if ($userPlan === null) {
            return false;
        }

        // Vérifie que le plan contient ce tool
        return $userPlan->getTools()->contains($tool);
    }
}
