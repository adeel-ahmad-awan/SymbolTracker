<?php

namespace App\Service;

use App\Entity\CompanySymbol;
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
use function PHPUnit\Framework\throwException;

/**
 *
 */
class HttpService
{
    /**
     *
     */
    const HTTP_GET = 'GET';

    /**
     * @var string
     */
    private string $apiUrl = 'https://pkgstore.datahub.io/core/nasdaq-listings/nasdaq-listed_json/data/a5bc7580d6176d60ac0b2142ca8d7df6/nasdaq-listed_json.json';

    /**
     * @var string
     */
    private string $symbolDataUrl = 'https://yh-finance.p.rapidapi.com/stock/v3/get-historical-data';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private HttpClientInterface $client;

    /**
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface
     */
    private ContainerBagInterface $params;

    /**
     * @param \Symfony\Contracts\HttpClient\HttpClientInterface                         $client
     * @param \Psr\Log\LoggerInterface                                                  $logger
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface $params
     */
    public function __construct(
        HttpClientInterface $client,
        LoggerInterface $logger,
        ContainerBagInterface $params
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->params = $params;
    }

    /**
     * @return null|array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getStockData()
    {
        try {
            $response = $this->client->request(
                self::HTTP_GET,
                $this->apiUrl
            );
            $statusCode = $response->getStatusCode();
            $content = $response->toArray();
            if ($statusCode == Response::HTTP_OK) {
                return ($content);
            } else {
                throw new \Exception('Error in Http Request');
            }
        } catch (\Exception $exception) {
            $this->logger->error('error: ' . $exception->getMessage());
        }
        return null;
    }

    /**
     * @param CompanySymbol $symbol
     * @return null|array|\Exception|TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getHistoricalQuote(CompanySymbol $symbol)
    {
        try {
            $response = $this->client->request(
                'GET',
                'https://www.alphavantage.co/query',
                [
                    'query' => [
                        'function' => 'TIME_SERIES_MONTHLY_ADJUSTED',
                        'symbol' => $symbol->getSymbol(),
                        'apikey' => $this->params->get('app.yahoo'),
                    ],
                ]
            );

            $statusCode = $response->getStatusCode();
            $content = $response->toArray();

            if ($statusCode == Response::HTTP_OK) {
                return $content;
            }
        } catch (\Exception $exception) {
            $this->logger->error('Error: ' . $exception->getMessage());
            return $exception;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Error: ' . $e->getMessage());
            return $e;
        }

        return null;
    }
}