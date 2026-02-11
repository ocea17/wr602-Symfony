<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Plan;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $planFree = $manager->getRepository(Plan::class)->findOneBy(['name' => 'FREE']);
        $planBasic = $manager->getRepository(Plan::class)->findOneBy(['name' => 'BASIC']);
        $planPremium = $manager->getRepository(Plan::class)->findOneBy(['name' => 'PREMIUM']);

        $user1 = new User();
        $user1->setEmail('test@test.com');
        $user1->setLastname('orange');
        $user1->setFirstname('Gina');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPlan($planFree);
        $hashedPassword = $this->passwordHasher->hashPassword($user1, 'test');
        $user1->setPassword($hashedPassword);
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('basic@test.com');
        $user2->setLastname('Hari');
        $user2->setFirstname('Bob');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPlan($planBasic);
        $hashedPassword = $this->passwordHasher->hashPassword($user2, 'test');
        $user2->setPassword($hashedPassword);
        $manager->persist($user2);

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