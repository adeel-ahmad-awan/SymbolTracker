<?php

namespace App\Command;

use App\Service\CompanySymbolService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * SyncCompanySymbolCommand
 */
#[AsCommand(
    name: 'app:sync-company-symbol',
    description: 'This command will sync the company symbols',
)]
class SyncCompanySymbolCommand extends Command
{
    /**
     * @var \App\Service\CompanySymbolService
     */
    private CompanySymbolService $companySymbolService;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param \App\Service\CompanySymbolService $companySymbolService
     */
    public function __construct(CompanySymbolService $companySymbolService, LoggerInterface $logger)
    {
        $this->companySymbolService = $companySymbolService;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->companySymbolService->setSymbols();
            $output->writeln('Command executed successfully');
        } catch (\Exception $exception) {
            $this->logger->error('Error at ' . $exception->getLine() . $exception->getFile());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
