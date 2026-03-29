<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Entity\Plan;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $user->setRoles(['ROLE_USER']);

        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('John', $user->getFirstname());
        $this->assertEquals('Doe', $user->getLastname());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testUserPlanAssociation(): void
    {
        $user = new User();
        $plan = new Plan();
        $plan->setName('PREMIUM');

        $user->setPlan($plan);

        $this->assertSame($plan, $user->getPlan());
        $this->assertEquals('PREMIUM', $user->getPlan()->getName());
    }

    public function testUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('user@test.com');

        $this->assertEquals('user@test.com', $user->getUserIdentifier());
    }
}
