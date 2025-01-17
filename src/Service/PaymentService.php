<?php

namespace App\Service;

use App\DataTransfer\TransactionDataTransfer;
use App\Service\PaymentIntegrationInterface;

class PaymentService
{
    public function __construct(
        private readonly PaymentIntegrationInterface $paymentClient
    ) { }

    public function processPayment(
        float $amount,
        string $currency,
        string $cardNumber,
        string $expMonth,
        string $expYear,
        string $cvv,
        string $cardholderName
    ): TransactionDataTransfer {
        return $this->paymentClient->processPayment(
            $amount,
            $currency,
            $cardNumber,
            $expMonth,
            $expYear,
            $cvv,
            $cardholderName
        );
    }
}
