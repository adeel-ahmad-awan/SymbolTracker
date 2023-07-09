<?php

namespace App\Controller;

use App\Form\CompanySymbolFormType;
use App\Service\CompanySymbolService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class CompanySymbolController extends AbstractController
{
    #[Route('/', name: 'app_company_symbol')]
    public function index(SessionInterface $session, CompanySymbolService $symbolService, Request $request): Response
    {
        $form = $this->createForm(CompanySymbolFormType::class);
        $form-> handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $processedData = $symbolService->getHistoricalQuote($data);
            $session->set('processedData', $processedData);
            return $this->redirectToRoute('app_show_data');
        }

        return $this->render('company_symbol/index.html.twig', [
            'controller_name' => 'CompanySymbolController',
            'form' => $form->createView()
        ]);
    }

    #[Route('/show', name: 'app_show_data')]
    public function displayData(SessionInterface $session, Request $request): Response
    {
        // getting data from session
        $processedData = $session->get('processedData');
        if (!$processedData) {
            return $this->redirectToRoute('app_company_symbol');
        }
//        $session->remove('processedData');


        // formatting data
        $xData = [];
        $yData = [];
        $open = [];
        $close = [];
        $high = [];
        $low = [];

        foreach ($processedData as $processedDatum) {
            $xData[] = $processedDatum['date'];
            $yData[] = $processedDatum['volume'];
            $open[] = $processedDatum['open'];
            $close[] = $processedDatum['close'];
            $high[] = $processedDatum['high'];
            $low[] = $processedDatum['low'];
        }

        $chartData = [
            'labels' => array_reverse($xData),
            'datasets' => [
                [
                    'label' => 'Volume',
                    'borderWidth' => 1,
                    'data' => array_reverse($yData),
                ],
                [
                    'label' => 'Open',
                    'borderWidth' => 1,
                    'data' => array_reverse($open),
                ],
                [
                    'label' => 'High',
                    'borderWidth' => 1,
                    'data' => array_reverse($high),
                ],
                [
                    'label' => 'Low',
                    'borderWidth' => 1,
                    'low' => array_reverse($low),
                ],
                [
                    'label' => 'Close',
                    'borderWidth' => 1,
                    'low' => array_reverse($close),
                ],
            ],
        ];

        return $this->render('company_symbol/show.html.twig', [
            'chartData' => $chartData,
        ]);
    }
}
