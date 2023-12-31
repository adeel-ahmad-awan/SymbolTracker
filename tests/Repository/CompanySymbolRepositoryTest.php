<?php

namespace App\Tests\Repository;

use App\Entity\CompanySymbol;
use App\Repository\CompanySymbolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CompanySymbolRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?CompanySymbolRepository $companySymbolRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->companySymbolRepository = $this->entityManager->getRepository(CompanySymbol::class);
        $this->entityManager->clear();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager = null;
        $this->companySymbolRepository = null;
    }

    public function testIsTableEmptyReturnsTrueWhenTableIsEmpty(): void
    {
        $result = $this->companySymbolRepository->isTableEmpty();
        $this->assertTrue($result);
    }

    public function testIsTableEmptyReturnsFalseWhenTableIsNotEmpty(): void
    {
        $companySymbol = new CompanySymbol();
        $companySymbol->setSymbol('AAPL');

        $this->entityManager->persist($companySymbol);
        $this->entityManager->flush();

        $result = $this->companySymbolRepository->isTableEmpty();

        $this->assertFalse($result);
    }
}
