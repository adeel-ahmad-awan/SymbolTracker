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
        // Create a mock for the EntityManager and HttpService
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $httpServiceMock = $this->createMock(HttpService::class);

        // Set up the CompanySymbolService instance with the mocks
        $companySymbolService = new CompanySymbolService($entityManagerMock, $httpServiceMock);

        // Set up the processed data
        $processedData = [
            'symbol' => 'AAPL',
            'email' => 'this@email.com',
            'quoteData' => [
                'Meta Data' => [
                    '1. Information' => 'Monthly Adjusted Prices and Volumes',
                    '2. Symbol' => 'AAPL',
                    '3. Last Refreshed' => '2024-02-09',
                    '4. Time Zone' => 'US/Eastern',
                ],
                'Monthly Adjusted Time Series' => [
                    '2024-02-09' => [
                        '1. open' => 180.05,
                        '2. high' => 182.88,
                        '3. low' => 178.32,
                        '4. close' => 180.67,
                        '5. adjusted close' => 180.67,
                        '6. volume' => 50165243,
                        '7. dividend amount' => 0.22,
                    ],
                ],
            ],
            'startDate' => '2023-06-20',
            'endDate' => '2024-02-11',
        ];

        // Call the method under test
        $result = $companySymbolService->getChartData($processedData);

        // Define expected chart data
        $expectedChartData = [
            'labels' => ['2024-02-09'],
            'datasets' => [
                [
                    'label' => 'Open',
                    'borderWidth' => 1,
                    'data' => [180.05],
                ],
                [
                    'label' => 'Close',
                    'borderWidth' => 1,
                    'data' => [180.67],
                ],
            ],
        ];

        // Assert that the result matches the expected chart data
        $this->assertEquals($expectedChartData, $result);
    }

}
