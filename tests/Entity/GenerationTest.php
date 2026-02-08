<?php

namespace App\Tests\Entity;

use App\Entity\Generation;
use PHPUnit\Framework\TestCase;

class GenerationTest extends TestCase
{
    public function testGetterAndSetter()
    {

        $generation = new Generation();

        $file = 'document.pdf';
        $createdAt = new \DateTimeImmutable();

        $generation->setFile($file);
        $generation->setCreatedAt($createdAt);

        $this->assertEquals($file, $generation->getFile());
        $this->assertEquals($createdAt, $generation->getCreatedAt());
    }
}