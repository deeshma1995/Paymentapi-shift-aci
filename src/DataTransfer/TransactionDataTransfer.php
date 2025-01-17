<?php

namespace App\DataTransfer;

class TransactionDataTransfer
{
    private string $transactionId;
    private string $dateCreated;
    private string $amount;
    private string $currency;
    private string $cardBin;

    public function __construct(
        string $transactionId,
        string $dateCreated,
        string $amount,
        string $currency,
        string $cardBin
    ) {
        $this->transactionId = $transactionId;
        $this->dateCreated = $dateCreated;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->cardBin = $cardBin;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getDateCreated(): string
    {
        return $this->dateCreated;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCardBin(): string
    {
        return $this->cardBin;
    }

    public function toArray(): array
    {
        return [
            'transactionId' => $this->transactionId,
            'dateCreated' => $this->dateCreated,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'cardBin' => $this->cardBin,
        ];
    }
}
