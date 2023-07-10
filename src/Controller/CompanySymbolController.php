<?php

namespace App\Controller;

use App\Form\CompanySymbolFormType;
use App\Repository\CompanySymbolRepository;
use App\Service\CompanySymbolService;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


class CompanySymbolController extends AbstractController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \App\Service\CompanySymbolService                          $symbolService
     * @param \Symfony\Component\HttpFoundation\Request                  $request
     * @param \App\Repository\CompanySymbolRepository                    $companySymbolRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/', name: 'app_company_symbol')]
    public function index(
        SessionInterface $session,
        CompanySymbolService $symbolService,
        Request $request,
        CompanySymbolRepository $companySymbolRepository
    ): Response
    {
        if ($companySymbolRepository->isTableEmpty()) {
            return $this->redirectToRoute('app_run_command');
        }

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

    /**
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \App\Service\EmailService                                  $emailService
     * @param \App\Service\CompanySymbolService                          $symbolService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/show', name: 'app_show_data')]
    public function displayData(SessionInterface $session, EmailService $emailService, CompanySymbolService $symbolService): Response
    {
        $processedData = $session->get('processedData');
        if (!$processedData) {
            $this->addFlash('error', 'Error in data');
            return $this->redirectToRoute('app_company_symbol');
        }

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

        $chartData = $symbolService->getChartData($processedData);

        return $this->render('company_symbol/show.html.twig', [
            'chartData' => $chartData,
            'symbol' => $processedData['symbol']
        ]);
    }

    /**
     * @param \App\Repository\CompanySymbolRepository $companySymbolRepository
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    #[Route('/run_command', name: 'app_run_command')]
    public function addData(
        CompanySymbolRepository $companySymbolRepository
    ) {
        if (!$companySymbolRepository->isTableEmpty()) {
            return $this->redirectToRoute('app_company_symbol');
        }

        return $this->render('company_symbol/run_command.twig', [
            'controller_name' => 'CompanySymbolController',
            'emptyTable' => "Please run the following command to set the company symbols in database before proceeding",
            'command' => "php bin/console app:sync-company-symbol"
        ]);
    }
}
