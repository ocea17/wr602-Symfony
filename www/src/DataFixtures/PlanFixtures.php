<?php

namespace App\DataFixtures;

use App\Entity\Plan;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlanFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $planFree = new Plan();
        $planFree->setName('FREE');
        $planFree->setDescription('Plan gratuit avec 2 PDFs par jour');
        $planFree->setLimitGeneration(2);
        $planFree->setRole('ROLE_USER');
        $planFree->setPrice(0);
        $planFree->setActive(true);
        $planFree->setCreatedAt(new DateTimeImmutable());
        $manager->persist($planFree);

        // plan basique
        $planBasic = new Plan();
        $planBasic->setName('BASIC');
        $planBasic->setDescription('Plan basique avec 20 PDFs par jour');
        $planBasic->setLimitGeneration(20);
        $planBasic->setRole('ROLE_USER');
        $planBasic->setPrice(9.99);
        $planBasic->setActive(true);
        $planBasic->setCreatedAt(new DateTimeImmutable());
        $manager->persist($planBasic);

        // plan premium
        $planPremium = new Plan();
        $planPremium->setName('PREMIUM');
        $planPremium->setDescription('Plan premium avec génération illimitée de PDFs');
        $planPremium->setLimitGeneration(null);
        $planPremium->setRole('ROLE_PREMIUM');
        $planPremium->setPrice(29.99);
        $planPremium->setActive(true);
        $planPremium->setCreatedAt(new DateTimeImmutable());
        $manager->persist($planPremium);

        $manager->flush();
    }
}
