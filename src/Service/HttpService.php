<?php

namespace App\Service;

use App\Entity\CompanySymbol;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
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
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
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
     * @param \App\Entity\CompanySymbol $symbol
     * @param                           $startDate
     * @param                           $endDate
     * @param                           $email
     *
     * @return null|array|\Exception|\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     */
    public function getHistoricalQuote(CompanySymbol $symbol, $startDate,$endDate,$email)
    {
        try {
            $response = $this->client->request(
                self::HTTP_GET,
                $this->symbolDataUrl,
                [
                    'headers' => [
                        'X-RapidAPI-Key' => $this->params->get('app.yahoo'),
                        'X-RapidAPI-Host' => 'yh-finance.p.rapidapi.com',
                    ],
                    'query' => [
                        'symbol' => $symbol->getSymbol(),
                    ],
                ]
            );

            $statusCode = $response->getStatusCode();
            $content = $response->toArray();
            if ($statusCode == Response::HTTP_OK) {
                return ($content);
            }
        } catch (\Exception $exception) {
            $this->logger->error('error: ' . $exception->getMessage());
            return ($exception);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('error: ' . $e->getMessage());
            return ($e);
        }
        return null;
    }
}