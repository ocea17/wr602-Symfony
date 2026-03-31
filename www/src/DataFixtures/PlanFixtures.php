<?php

namespace App\DataFixtures;

use App\Entity\Plan;
use App\Entity\Tool;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PlanFixtures extends Fixture implements DependentFixtureInterface
{
    public const PLAN_FREE = 'plan-free';
    public const PLAN_BASIC = 'plan-basic';
    public const PLAN_PREMIUM = 'plan-premium';

    public function load(ObjectManager $manager): void
    {
        // Plan FREE
        $freePlan = new Plan();
        $freePlan->setName('FREE');
        $freePlan->setPrice(0);
        $freePlan->setDescription('Plan gratuit avec 2 PDFs par jour');
        $freePlan->setLimitGeneration(2);
        $freePlan->setActive(true);
        $freePlan->setLimitStorage(null);
        $freePlan->setStripePriceId(null);

        $freePlan->addTool($this->getReference(ToolFixtures::TOOL_URL, Tool::class));
        $freePlan->addTool($this->getReference(ToolFixtures::TOOL_WYSIWYG, Tool::class));
        $freePlan->addTool($this->getReference(ToolFixtures::TOOL_TEXT, Tool::class));

        $manager->persist($freePlan);
        $this->addReference(self::PLAN_FREE, $freePlan);

        // Plan BASIC
        $basicPlan = new Plan();
        $basicPlan->setName('BASIC');
        $basicPlan->setPrice(9.99);
        $basicPlan->setDescription('Plan basique avec 20 PDFs par jour');
        $basicPlan->setLimitGeneration(20);
        $basicPlan->setActive(true);
        $basicPlan->setLimitStorage(50);
        $basicPlan->setStripePriceId('price_1T9n1SKEBKL4t2dRszcrxVE5');

        $basicPlan->addTool($this->getReference(ToolFixtures::TOOL_URL, Tool::class));
        $basicPlan->addTool($this->getReference(ToolFixtures::TOOL_HTML, Tool::class));
        $basicPlan->addTool($this->getReference(ToolFixtures::TOOL_WYSIWYG, Tool::class));
        $basicPlan->addTool($this->getReference(ToolFixtures::TOOL_MERGE, Tool::class));
        $basicPlan->addTool($this->getReference(ToolFixtures::TOOL_TEXT, Tool::class));

        $manager->persist($basicPlan);
        $this->addReference(self::PLAN_BASIC, $basicPlan);

        // Plan PREMIUM
        $premiumPlan = new Plan();
        $premiumPlan->setName('PREMIUM');
        $premiumPlan->setPrice(29.99);
        $premiumPlan->setDescription('Plan premium avec 200 PDFs par jour');
        $premiumPlan->setLimitGeneration(200);
        $premiumPlan->setActive(true);
        $premiumPlan->setLimitStorage(500);
        $premiumPlan->setStripePriceId('price_1T9n32KEBKL4t2dRf4COK6iy');

        $premiumPlan->addTool($this->getReference(ToolFixtures::TOOL_URL, Tool::class));
        $premiumPlan->addTool($this->getReference(ToolFixtures::TOOL_HTML, Tool::class));
        $premiumPlan->addTool($this->getReference(ToolFixtures::TOOL_WYSIWYG, Tool::class));
        $premiumPlan->addTool($this->getReference(ToolFixtures::TOOL_MERGE, Tool::class));
        $premiumPlan->addTool($this->getReference(ToolFixtures::TOOL_SPLIT, Tool::class));
        $premiumPlan->addTool($this->getReference(ToolFixtures::TOOL_SCREENSHOT, Tool::class));
        $premiumPlan->addTool($this->getReference(ToolFixtures::TOOL_OFFICE, Tool::class));
        $premiumPlan->addTool($this->getReference(ToolFixtures::TOOL_TEXT, Tool::class));

        $manager->persist($premiumPlan);
        $this->addReference(self::PLAN_PREMIUM, $premiumPlan);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ToolFixtures::class,
        ];
    }
}
