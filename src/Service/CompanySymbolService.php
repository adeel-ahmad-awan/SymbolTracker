<?php

namespace App\Service;

use App\Entity\CompanySymbol;
use App\Repository\CompanySymbolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class CompanySymbolService
{
    const HTTP_GET = 'GET';

    private string $apiUrl = 'https://pkgstore.datahub.io/core/nasdaq-listings/nasdaq-listed_json/data/a5bc7580d6176d60ac0b2142ca8d7df6/nasdaq-listed_json.json';

    private string $symbolDataUrl = 'https://yh-finance.p.rapidapi.com/stock/v3/get-historical-data';

    private HttpClientInterface $client;

    private EntityManagerInterface $entityManager;

    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $client,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        ContainerBagInterface $params
    )
    {
        $this->client = $client;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function getHistoricalQuote($data)
    {
        $symbol = $data["symbol"];
        $startDate = $data["start_date"];
        $endDate = $data["end_date"];
        $email = $data["email"];

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
                return [
                    'symbol' => $symbol->getSymbol(),
                    "email" => $email,
                    "quoteData" => $this->filterHistoricalQuoteData($content, $startDate, $endDate),
                    "startDate" => $startDate->format('Y-m-d'),
                    "endDate" => $endDate->format('Y-m-d'),
                ];
            }
        } catch (\Exception $exception) {
            $this->logger->error('error: ' . $exception->getMessage());
            return ($exception);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('error: ' . $e->getMessage());
            return ($e);
        }
    }


    public function filterHistoricalQuoteData($dataArray, $startDate, $endDate)
    {
        $prices = [];
        foreach ($dataArray['prices'] as $key => $value){
            $timestampDate = date('Y-m-d', $value['date']);
            $timestampDate = \DateTime::createFromFormat('Y-m-d', $timestampDate);
            if ($timestampDate >= $startDate && $timestampDate <= $endDate) {
                $prices[] = [
                    'date' => $timestampDate->format('Y/m/d'),
                    'open' => $value['open'],
                    'high' => $value['high'],
                    'low' => $value['low'],
                    'close' => $value['close'],
                    'volume' => $value['volume'],
                    'adjclose' => $value['adjclose'],
                ];
            }
        }
        return $prices;
    }

    public function getSymbols()
    {
        try {
            $response = $this->client->request(
                self::HTTP_GET,
                $this->apiUrl
            );

            $statusCode = $response->getStatusCode();
            $content = $response->toArray();

            if ($statusCode == Response::HTTP_OK) {
                foreach ($content as $key => $value){
                    if (array_key_exists('Symbol', $value)) {
                        $companySymbol = new CompanySymbol();
                        $companySymbol->setSymbol($value['Symbol']);
                        $this->entityManager->persist($companySymbol);
                        if ($key % 50 == 0) {
                            $this->entityManager->flush();
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error('error: ' . $exception->getMessage());
        }
    }

}