<?php

namespace App\Service;

use App\DataTransfer\TransactionDataTransfer;

interface PaymentIntegrationInterface
{
    public function processPayment(
        float $amount,
        string $currency,
        string $cardNumber,
        string $cardExpMonth,
        string $cardExpYear,
        string $cardCvv,
        string $cardholderName
    ): TransactionDataTransfer;
}