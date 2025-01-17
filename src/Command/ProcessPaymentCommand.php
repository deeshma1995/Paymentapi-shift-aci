<?php

namespace App\Command;

use App\Integration\Shift4IntegrationManager;
use App\Integration\ACIIntegrationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:example')]
class ProcessPaymentCommand extends Command
{
    public function __construct(
        private readonly Shift4IntegrationManager $shift4IntegrationManager,
        private readonly ACIIntegrationManager $aciIntegrationManager,
        private readonly LoggerInterface $logger
    ){
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Process payment via Shift4 or ACI')
            ->addArgument('provider', InputArgument::REQUIRED, 'The payment provider (shift4|aci)')
            ->addArgument('amount', InputArgument::REQUIRED, 'The amount to charge')
            ->addArgument('currency', InputArgument::REQUIRED, 'The currency to use (e.g., USD)')
            ->addArgument('cardNumber', InputArgument::REQUIRED, 'Card number')
            ->addArgument('expMonth', InputArgument::REQUIRED, 'Card expiry month')
            ->addArgument('expYear', InputArgument::REQUIRED, 'Card expiry year')
            ->addArgument('cvv', InputArgument::REQUIRED, 'Card CVV')
            ->addArgument('cardholderName', InputArgument::REQUIRED, 'Cardholder name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $provider = $input->getArgument('provider');
        $amount = $input->getArgument('amount');
        $currency = $input->getArgument('currency');
        $cardNumber = $input->getArgument('cardNumber');
        $expMonth = $input->getArgument('expMonth');
        $expYear = $input->getArgument('expYear');
        $cvv = $input->getArgument('cvv');
        $cardholderName = $input->getArgument('cardholderName');

        try {
            $response = match ($provider) {
                'shift4' => $this->shift4IntegrationManager->processPayment(
                    (int )$amount, $currency, $cardNumber, $expMonth, $expYear, $cvv, $cardholderName
                ),
                'aci' => $this->aciIntegrationManager->processPayment(
                    (int) $amount, $currency, $cardNumber, $expMonth, $expYear, $cvv, $cardholderName
                ),
                default => throw new \RuntimeException('Invalid provider specified. Use "shift4" or "aci".'),
            };

            $io->success('Payment processed successfully.');
            $io->table(
                ['Transaction ID', 'Created At', 'Amount', 'Currency', 'Card BIN'],
                [[
                    $response->getTransactionId(),
                    $response->getDateCreated(),
                    $response->getAmount(),
                    $response->getCurrency(),
                    $response->getCardBin(),
                ]]
            );

            return Command::SUCCESS;
        } catch (\RuntimeException $e) {
            $this->logger->error('Payment processing failed', ['error' => $e->getMessage()]);
            $io->error('Payment processing failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
