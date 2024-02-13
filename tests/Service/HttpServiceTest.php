<?php

namespace App\Tests\Service;

use App\Entity\CompanySymbol;
use App\Service\HttpService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpServiceTest extends TestCase
{

    /**
     * @var (object&MockObject)|MockObject|HttpClientInterface|(HttpClientInterface&object&MockObject)|(HttpClientInterface&MockObject)
     */
    private $httpClientMock;

    /**
     * @var (object&MockObject)|MockObject|LoggerInterface|(LoggerInterface&object&MockObject)|(LoggerInterface&MockObject)
     */
    private $loggerMock;

    /**
     * @var (object&MockObject)|MockObject|ContainerBagInterface|(ContainerBagInterface&object&MockObject)|(ContainerBagInterface&MockObject)
     */
    private $parameterBagMock;

    /**
     * @var string
     */
    private string $apiUrl = 'https://pkgstore.datahub.io/core/nasdaq-listings/nasdaq-listed_json/data/a5bc7580d6176d60ac0b2142ca8d7df6/nasdaq-listed_json.json';

    /**
     * @var string
     */
    private string $symbolDataUrl = 'https://www.alphavantage.co/query';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->parameterBagMock = $this->createMock(ContainerBagInterface::class);
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
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

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
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

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
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
            ->with(
                HttpService::HTTP_GET,
                'https://www.alphavantage.co/query',
                [
                    'query' => [
                        'function' => 'TIME_SERIES_MONTHLY_ADJUSTED',
                        'symbol' => 'AAPL',
                        'apikey' => 'rapid_api_key',
                    ],
                ]
            )
            ->willReturn($responseMock);

        $this->parameterBagMock->expects($this->once())
            ->method('get')
            ->with('app.yahoo')
            ->willReturn('rapid_api_key');

        $result = $httpService->getHistoricalQuote($symbolMock);

        $this->assertSame(['data'], $result);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
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
