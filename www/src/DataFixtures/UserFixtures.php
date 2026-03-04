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
        // Récupère les plans via les références AVEC le 2ème paramètre
        $planFree = $this->getReference(PlanFixtures::PLAN_FREE, Plan::class);
        $planBasic = $this->getReference(PlanFixtures::PLAN_BASIC, Plan::class);
        $planPremium = $this->getReference(PlanFixtures::PLAN_PREMIUM, Plan::class);

        // User FREE
        $user1 = new User();
        $user1->setEmail('test@test.com');
        $user1->setLastname('Orange');
        $user1->setFirstname('Gina');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPlan($planFree);
        $hashedPassword = $this->passwordHasher->hashPassword($user1, 'test');
        $user1->setPassword($hashedPassword);
        $manager->persist($user1);

        // User BASIC
        $user2 = new User();
        $user2->setEmail('basic@test.com');
        $user2->setLastname('Hari');
        $user2->setFirstname('Bob');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPlan($planBasic);
        $hashedPassword = $this->passwordHasher->hashPassword($user2, 'test');
        $user2->setPassword($hashedPassword);
        $manager->persist($user2);

        // User PREMIUM
        $user3 = new User();
        $user3->setEmail('premium@test.com');
        $user3->setLastname('Cristel');
        $user3->setFirstname('Line');
        $user3->setRoles(['ROLE_PREMIUM']);
        $user3->setPlan($planPremium);
        $hashedPassword = $this->passwordHasher->hashPassword($user3, 'test');
        $user3->setPassword($hashedPassword);
        $manager->persist($user3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PlanFixtures::class,
        ];
    }
}
