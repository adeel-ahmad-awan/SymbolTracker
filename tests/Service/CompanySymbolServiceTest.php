<?php

namespace App\Tests\Service;

use App\Entity\CompanySymbol;
use App\Service\CompanySymbolService;
use App\Service\HttpService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CompanySymbolServiceTest extends TestCase
{
    private $entityManagerMock;
    private $httpServiceMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->httpServiceMock = $this->createMock(HttpService::class);
    }

    public function testGetHistoricalQuoteReturnsProcessedData(): void
    {
        $companySymbolMock = $this->createMock(CompanySymbol::class);

        $companySymbolMock->expects($this->once())
            ->method('getSymbol')
            ->willReturn('AAPL');

        $data = [
            'symbol' => $companySymbolMock,
            'start_date' => new \DateTime('2022-01-01'),
            'end_date' => new \DateTime('2022-01-31'),
            'email' => 'test@example.com',
        ];

        $content = [
            'prices' => [
                [
                    'date' => 1688736600,
                    'open' => 100,
                    'high' => 110,
                    'low' => 90,
                    'close' => 105,
                    'volume' => 1000000,
                    'adjclose' => 100,
                ],
                [
                    'date' => 1688650200,
                    'open' => 106,
                    'high' => 112,
                    'low' => 100,
                    'close' => 108,
                    'volume' => 900000,
                    'adjclose' => 106,
                ],
            ],
        ];

        $this->httpServiceMock->expects($this->once())
            ->method('getHistoricalQuote')
            ->with($companySymbolMock, new \DateTime('2022-01-01'), new \DateTime('2022-01-31'), 'test@example.com')
            ->willReturn($content);

        $companySymbolService = new CompanySymbolService($this->entityManagerMock, $this->httpServiceMock);
        $result = $companySymbolService->getHistoricalQuote($data);
        $this->assertIsArray($result);
    }

    public function testFilterHistoricalQuoteDataReturnsFilteredData(): void
    {
        $companySymbolService = new CompanySymbolService($this->entityManagerMock, $this->httpServiceMock);

        $dataArray = [
            'prices' => [
                [
                    'date' => strtotime('2022-01-01'),
                    'open' => 100,
                    'high' => 110,
                    'low' => 90,
                    'close' => 105,
                    'volume' => 1000000,
                    'adjclose' => 100,
                ],
                [
                    'date' => strtotime('2022-01-02'),
                    'open' => 106,
                    'high' => 112,
                    'low' => 100,
                    'close' => 108,
                    'volume' => 900000,
                    'adjclose' => 106,
                ],
            ],
        ];

        $startDate = new \DateTime('2022-01-01');
        $endDate = new \DateTime('2022-01-31');


        $result = $companySymbolService->filterHistoricalQuoteData($dataArray, $startDate, $endDate);

        $this->assertIsArray($result);
    }

    public function testSetSymbolsDoesNotReturnValue(): void
    {
        $symbolData = [
            'symbol' => ['symbol'],
            'symbol1' => ['symbol1'],
            'symbol2' => ['symbo2'],
        ];

        $this->httpServiceMock->expects($this->once())
            ->method('getStockData')
            ->willReturn($symbolData);

        $this->entityManagerMock->expects($this->exactly(0))
            ->method('persist');

        $this->entityManagerMock->expects($this->exactly(0))
            ->method('flush');

        $companySymbolService = new CompanySymbolService($this->entityManagerMock, $this->httpServiceMock);
        $result = $companySymbolService->setSymbols();

        $this->assertNull($result);
    }


    public function testGetChartDataReturnsExpectedChartData(): void
    {
        $companySymbolService = new CompanySymbolService($this->entityManagerMock, $this->httpServiceMock);

        $processedData = [
            'quoteData' => [
                [
                    'date' => '2022/01/01',
                    'open' => 100,
                    'close' => 105,
                ],
                [
                    'date' => '2022/01/02',
                    'open' => 106,
                    'close' => 108,
                ],
            ],
        ];
        $result = $companySymbolService->getChartData($processedData);
        $this->assertIsArray($result);
    }
}
