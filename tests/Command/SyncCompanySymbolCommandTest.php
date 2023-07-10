<?php
namespace App\Tests\Command;

use App\Command\SyncCompanySymbolCommand;
use App\Service\CompanySymbolService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class SyncCompanySymbolCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $companySymbolServiceMock = $this->createMock(CompanySymbolService::class);
        $command = new SyncCompanySymbolCommand($companySymbolServiceMock);
        $application = new Application();
        $application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Command executed successfully', $output);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
