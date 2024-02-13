<?php

namespace App\Service;

use App\Entity\CompanySymbol;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 *
 */
class CompanySymbolService
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var \App\Service\HttpService
     */
    private HttpService $httpService;

    private const DATABASEFLUSHRATE = 50;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Service\HttpService             $httpService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        HttpService $httpService
    )
    {
        $this->entityManager = $entityManager;
        $this->httpService = $httpService;
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function getHistoricalQuote($data)
    {
        try {
            $symbol = $data["symbol"];
            $startDate = $data["start_date"];
            $endDate = $data["end_date"];
            $email = $data["email"];

            $content = $this->httpService
                ->getHistoricalQuote($symbol, $startDate, $endDate, $email);

            return [
                'symbol' => $symbol->getSymbol(),
                "email" => $email,
                "quoteData" => $content, // Assuming the content is returned in the desired format
                "startDate" => $startDate->format('Y-m-d'),
                "endDate" => $endDate->format('Y-m-d'),
            ];

        } catch (\Exception $exception) {
            dd($exception);
        }
    }

    /**
     * @param $dataArray
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
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

    /**
     * @return null
     */
    public function setSymbols()
    {
        $symbolData = $this->httpService->getStockData();
        foreach ($symbolData as $key => $value){
            if (array_key_exists('Symbol', $value)) {
                $companySymbol = new CompanySymbol();
                $companySymbol->setSymbol($value['Symbol']);
                $this->entityManager->persist($companySymbol);
                if ($key % self::DATABASEFLUSHRATE == 0) {
                    $this->entityManager->flush();
                }
            }
        }
        return null;
    }

    /**
     * @param $processedData
     *
     * @return array
     */
    public function getChartData($processedData)
    {
        // Extracting quote data from processed data
        $quoteData = $processedData['quoteData']['Monthly Adjusted Time Series'];


        // Initialize arrays to store chart data
        $labels = [];
        $openData = [];
        $closeData = [];

        // Loop through the quote data
        foreach ($quoteData as $date => $quote) {
            // Extracting required values
            $labels[] = $date;
            $openData[] = (float) $quote['1. open'];
            $closeData[] = (float) $quote['4. close'];
        }

        // Reverse arrays to maintain chronological order
        $labels = array_reverse($labels);
        $openData = array_reverse($openData);
        $closeData = array_reverse($closeData);

        // Constructing chart data array
        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Open',
                    'borderWidth' => 1,
                    'data' => $openData,
                ],
                [
                    'label' => 'Close',
                    'borderWidth' => 1,
                    'data' => $closeData,
                ],
            ],
        ];

        return $chartData;
    }

}