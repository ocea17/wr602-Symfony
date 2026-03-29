<?php

namespace App\DataFixtures;

use App\Entity\Plan;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $planFree = $this->getReference(PlanFixtures::PLAN_FREE, Plan::class);
        $planBasic = $this->getReference(PlanFixtures::PLAN_BASIC, Plan::class);
        $planPremium = $this->getReference(PlanFixtures::PLAN_PREMIUM, Plan::class);

        $colors = ['#3B82F6', '#8B5CF6', '#EC4899', '#10B981', '#F59E0B'];

        // User free
        $userFree = new User();
        $userFree->setEmail('demo.free@pdfgenerator.com');
        $userFree->setFirstname('Demo');
        $userFree->setLastname('Free');
        $userFree->setRoles(['ROLE_USER']);
        $userFree->setPlan($planFree);
        $userFree->setAvatarColor($colors[0]);
        $hashedPassword = $this->passwordHasher->hashPassword($userFree, 'demo123');
        $userFree->setPassword($hashedPassword);
        $manager->persist($userFree);

        // User basic
        $userBasic = new User();
        $userBasic->setEmail('demo.basic@pdfgenerator.com');
        $userBasic->setFirstname('Demo');
        $userBasic->setLastname('Basic');
        $userBasic->setRoles(['ROLE_USER']);
        $userBasic->setPlan($planBasic);
        $userBasic->setAvatarColor($colors[1]);
        $hashedPassword = $this->passwordHasher->hashPassword($userBasic, 'demo123');
        $userBasic->setPassword($hashedPassword);
        $manager->persist($userBasic);

        // User premium
        $userPremium = new User();
        $userPremium->setEmail('demo.premium@pdfgenerator.com');
        $userPremium->setFirstname('Demo');
        $userPremium->setLastname('Premium');
        $userPremium->setRoles(['ROLE_USER']);
        $userPremium->setPlan($planPremium);
        $userPremium->setAvatarColor($colors[2]);
        $hashedPassword = $this->passwordHasher->hashPassword($userPremium, 'demo123');
        $userPremium->setPassword($hashedPassword);
        $manager->persist($userPremium);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PlanFixtures::class,
        ];
    }
}