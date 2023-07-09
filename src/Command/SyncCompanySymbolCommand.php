<?php

namespace App\Command;

use App\Service\CompanySymbolService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sync-company-symbol',
    description: 'This command will sync the company symbols',
)]
class SyncCompanySymbolCommand extends Command
{
    private CompanySymbolService $companySymbolService;

    public function __construct(CompanySymbolService $companySymbolService)
    {
        $this->companySymbolService = $companySymbolService;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->companySymbolService->setSymbols();
        } catch (\Exception $exception) {
            dump('Error at ', $exception->getLine(), $exception->getFile());
            dump($exception->getCode());
            dump($exception->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
