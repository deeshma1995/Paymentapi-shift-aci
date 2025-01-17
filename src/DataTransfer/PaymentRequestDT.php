<?php

namespace App\DataTransfer;

use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentRequestDT
{
    #[Assert\NotBlank(message: "Amount is required.")]
    #[Assert\Type(type: "numeric", message: "Amount must be numeric.")]
    #[Assert\GreaterThan(value: 0, message: "Amount must be greater than 0.")]
    public int $amount;

    #[Assert\NotBlank(message: "Currency is required.")]
    #[Assert\Length(
        min: 3,
        max: 3,
        minMessage: "Currency must be exactly 3 characters.",
        maxMessage: "Currency must be exactly 3 characters."
    )]
    public string $currency;

    #[Assert\NotBlank(message: "Card number is required.")]
    #[Assert\Length(
        min: 13,
        max: 19,
        minMessage: "Card number must be at least 13 digits.",
        maxMessage: "Card number cannot be longer than 19 digits."
    )]
    #[Assert\Regex(pattern: "/^\d+$/", message: "Card number must be numeric.")]
    public string $cardNumber;

    #[Assert\NotBlank(message: "Expiration month is required.")]
    #[Assert\Length(
        min: 2,
        max: 2,
        minMessage: "Expiration month must be exactly 2 digits.",
        maxMessage: "Expiration month must be exactly 2 digits."
    )]
    #[Assert\Regex(pattern: "/^\d{2}$/", message: "Expiration month must be numeric.")]
    public string $expMonth;

    #[Assert\NotBlank(message: "Expiration year is required.")]
    #[Assert\Length(
        min: 4,
        max: 4,
        minMessage: "Expiration year must be exactly 4 digits.",
        maxMessage: "Expiration year must be exactly 4 digits."
    )]
    #[Assert\Regex(pattern: "/^\d{4}$/", message: "Expiration year must be numeric.")]
    public string $expYear;

    #[Assert\NotBlank(message: "CVV is required.")]
    #[Assert\Length(
        min: 3,
        max: 4,
        minMessage: "CVV must be at least 3 digits.",
        maxMessage: "CVV must be at most 4 digits."
    )]
    #[Assert\Regex(pattern: "/^\d{3,4}$/", message: "CVV must be numeric.")]
    public string $cvv;

    #[Assert\NotBlank(message: "Cardholder name is required.")]
    public string $cardholderName;

    public function __construct(InputBag $data)
    {
        $this->amount = $data->get('amount');
        $this->currency = $data->get('currency');
        $this->cardNumber = $data->get('card_number');
        $this->expMonth = $data->get('exp_month');
        $this->expYear = $data->get('exp_year');
        $this->cvv = $data->get('cvv');
        $this->cardholderName = $data->get('cardholder_name');
    }
}
