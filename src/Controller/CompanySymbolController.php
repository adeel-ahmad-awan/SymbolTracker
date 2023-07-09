<?php

namespace App\Controller;

use App\Form\CompanySymbolFormType;
use App\Service\CompanySymbolService;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


class CompanySymbolController extends AbstractController
{
    #[Route('/', name: 'app_company_symbol')]
    public function index(
        SessionInterface $session,
        CompanySymbolService $symbolService,
        Request $request,
    ): Response
    {
        $form = $this->createForm(CompanySymbolFormType::class);
        $form-> handleRequest($request);
        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $processedData = $symbolService->getHistoricalQuote($data);
                $session->set('processedData', $processedData);
                return $this->redirectToRoute('app_show_data');
            }
        } catch (\Exception $exception) {
            $this->addFlash(
                'error',
                'An error occurred.'
            );
        }
        return $this->render('company_symbol/index.html.twig', [
            'controller_name' => 'CompanySymbolController',
            'form' => $form->createView()
        ]);
    }

    #[Route('/show', name: 'app_show_data')]
    public function displayData(SessionInterface $session, EmailService $emailService): Response
    {
        $processedData = $session->get('processedData');
        if (!$processedData) {
            $this->addFlash('error', 'Error in data');
            return $this->redirectToRoute('app_company_symbol');
        }

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


        try {
            $emailService->sendEmail(
                $processedData['email'],
                "From " .  $processedData['startDate'] ." to " . $processedData['endDate'],
                $processedData['symbol']
            );
        } catch (\Exception $exception) {
            $this->addFlash(
                'error',
                'An error occurred in sending email.'
            );
        }

        return $this->render('company_symbol/show.html.twig', [
            'chartData' => $chartData,
            'symbol' => $processedData['symbol']
        ]);
    }
}
