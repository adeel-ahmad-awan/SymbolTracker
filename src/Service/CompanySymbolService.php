<?php

namespace App\Service;

use App\Entity\CompanySymbol;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class CompanySymbolService
{
    private EntityManagerInterface $entityManager;

    private HttpService $httpService;


    public function __construct(
        EntityManagerInterface $entityManager,
        HttpService $httpService
    )
    {
        $this->entityManager = $entityManager;
        $this->httpService = $httpService;
    }

    public function getHistoricalQuote($data)
    {
        $symbol = $data["symbol"];
        $startDate = $data["start_date"];
        $endDate = $data["end_date"];
        $email = $data["email"];
        $content = $this->httpService
            ->getHistoricalQuote($symbol,$startDate,$endDate,$email);
        return [
            'symbol' => $symbol->getSymbol(),
            "email" => $email,
            "quoteData" => $this->filterHistoricalQuoteData($content, $startDate, $endDate),
            "startDate" => $startDate->format('Y-m-d'),
            "endDate" => $endDate->format('Y-m-d'),
        ];
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

    public function setSymbols()
    {
        $symbolData = $this->httpService->getStockData();
        foreach ($symbolData as $key => $value){
            if (array_key_exists('Symbol', $value)) {
                $companySymbol = new CompanySymbol();
                $companySymbol->setSymbol($value['Symbol']);
                $this->entityManager->persist($companySymbol);
                if ($key % 50 == 0) {
                    $this->entityManager->flush();
                }
            }
        }
        return null;
    }

    public function getChartData($processedData)
    {
        $chartData = [
            'labels' => array_reverse(array_column($processedData['quoteData'], 'date')),
            'datasets' => [
                [
                    'label' => 'Open',
                    'borderWidth' => 1,
                    'data' => array_reverse(array_column($processedData['quoteData'], 'open')),
                ],
                [
                    'label' => 'Close',
                    'borderWidth' => 1,
                    'data' => array_reverse(array_column($processedData['quoteData'], 'close')),
                ],
            ],
        ];
        return ($chartData);
    }

}