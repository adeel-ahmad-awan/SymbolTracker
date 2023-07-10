<?php

namespace App\Tests\Service;

use App\Entity\CompanySymbol;
use App\Service\HttpService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpServiceTest extends TestCase
{
    private $httpClientMock;
    private $loggerMock;
    private $parameterBagMock;

    private string $apiUrl = 'https://pkgstore.datahub.io/core/nasdaq-listings/nasdaq-listed_json/data/a5bc7580d6176d60ac0b2142ca8d7df6/nasdaq-listed_json.json';

    private string $symbolDataUrl = 'https://yh-finance.p.rapidapi.com/stock/v3/get-historical-data';

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->parameterBagMock = $this->createMock(ContainerBagInterface::class);
    }

    public function testGetStockDataReturnsContentOnSuccess(): void
    {
        $httpService = new HttpService(
            $this->httpClientMock,
            $this->loggerMock,
            $this->parameterBagMock
        );

        $responseMock = $this->createMock(ResponseInterface::class);

        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_OK);
        $responseMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['somedata']);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                HttpService::HTTP_GET,
                $this->apiUrl
            )
            ->willReturn($responseMock);

        $result = $httpService->getStockData();

        $this->assertSame(['somedata'], $result);
    }

    public function testGetStockDataReturnsNullOnError(): void
    {
        $httpService = new HttpService(
            $this->httpClientMock,
            $this->loggerMock,
            $this->parameterBagMock
        );

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception('Error in Http Request'));

        $this->loggerMock->expects($this->once())
            ->method('error');

        $result = $httpService->getStockData();

        $this->assertNull($result);
    }

    public function testGetHistoricalQuoteReturnsContentOnSuccess(): void
    {
        $httpService = new HttpService(
            $this->httpClientMock,
            $this->loggerMock,
            $this->parameterBagMock
        );

        $symbolMock = $this->createMock(CompanySymbol::class);
        $symbolMock->expects($this->once())
            ->method('getSymbol')
            ->willReturn('AAPL');

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_OK);
        $responseMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['data']);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(HttpService::HTTP_GET, $this->symbolDataUrl, [
                'headers' => [
                    'X-RapidAPI-Key' => 'rapid_api_key',
                    'X-RapidAPI-Host' => 'yh-finance.p.rapidapi.com',
                ],
                'query' => [
                    'symbol' => 'AAPL',
                ],
            ])
            ->willReturn($responseMock);

        $this->parameterBagMock->expects($this->once())
            ->method('get')
            ->with('app.yahoo')
            ->willReturn('rapid_api_key');

        $result = $httpService->getHistoricalQuote($symbolMock, '2022-01-01', '2023-01-01', 'test@email.com');

        $this->assertSame(['data'], $result);
    }

    public function testGetHistoricalQuoteReturnsExceptionOnError(): void
    {
        $httpService = new HttpService(
            $this->httpClientMock,
            $this->loggerMock,
            $this->parameterBagMock
        );

        $symbolMock = $this->createMock(CompanySymbol::class);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception('Some error'));

        $this->loggerMock->expects($this->once())
            ->method('error');

        $result = $httpService->getHistoricalQuote($symbolMock, '2022-01-01', '2023-01-01', 'test@email.com');

        $this->assertInstanceOf(\Exception::class, $result);
    }
}
