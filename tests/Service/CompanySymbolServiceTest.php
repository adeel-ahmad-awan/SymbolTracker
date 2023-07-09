<?php

namespace App\Tests\Service;

use App\Tests\DatabasePrimer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CompanySymbolServiceTest extends KernelTestCase
{
    private $entityManager;
    protected function setUp(): void
    {
        $container = static::getContainer();
    }

    public function testItWorks()
    {
       $this->assertTrue(true);
    }

}