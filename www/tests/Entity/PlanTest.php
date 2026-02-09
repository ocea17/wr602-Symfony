<?php

namespace App\Tests\Entity;

use App\Entity\Plan;
use PHPUnit\Framework\TestCase;

class PlanTest extends TestCase
{
    public function testGetterAndSetter()
    {

        $plan = new Plan();

        $name = 'PREMIUM';
        $description = 'Plan premium';
        $price = 29.99;
        $role = 'ROLE_PREMIUM';
        $active = true;

        $plan->setName($name);
        $plan->setDescription($description);
        $plan->setPrice($price);
        $plan->setRole($role);
        $plan->setActive($active);

        $this->assertEquals($name, $plan->getName());
        $this->assertEquals($description, $plan->getDescription());
        $this->assertEquals($price, $plan->getPrice());
        $this->assertEquals($role, $plan->getRole());
        $this->assertEquals($active, $plan->isActive());
    }
}