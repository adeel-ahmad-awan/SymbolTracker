<?php

namespace App\Tests\Controller;

use App\Entity\CompanySymbol;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CompanySymbolControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');
        $this->assertResponseRedirects('/run_command');

        $client->request('POST', '/', ['company_symbol_form' => [
            'symbol' => 'AAPL',
            'startDate' => '2023-01-01',
            'endDate' => '2022-12-31', // Invalid end date
            'email' => 'test@example.com',
        ]]);
        $this->assertResponseStatusCodeSame(302);
    }

    public function testShowData(): void
    {
        $client = static::createClient();

        // Test when processedData is not set in the session
        $client->request('GET', '/show');
        $this->assertResponseRedirects('/');


        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->method('get')->willReturn([
            'symbol' => 'AAPL',
            'startDate' => '2023-01-01',
            'endDate' => '2023-01-31',
            'email' => 'test@example.com',
        ]);
        $client->getContainer()->set('session', $sessionMock);

        // Test when processedData is set in the session
        $client->request('GET', '/show');
        $this->assertResponseStatusCodeSame(302);
    }

//    public function testRunCommand(): void
//    {
//        $client = static::createClient();
//
//        // Test when the company symbol table is empty
//        $container = $client->getContainer();
//        $entityManager = $container->get('doctrine')->getManager();
//        $connection = $entityManager->getConnection();
//        $tableName = $entityManager->getClassMetadata(CompanySymbol::class)->getTableName();
//
//        $connection->executeStatement('TRUNCATE TABLE '.$tableName);
//        $client->request('GET', '/run_command');
//        $this->assertResponseRedirects('/');
//    }


    public function testRunCommandNoRedirect(): void {
        $client = static::createClient();
        $crawler = $client->request('GET', '/run_command');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }
}
