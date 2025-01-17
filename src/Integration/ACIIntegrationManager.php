<?php

namespace App\Integration;

use App\DataTransfer\TransactionDataTransfer;
use App\Service\PaymentIntegrationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ACIIntegrationManager implements PaymentIntegrationInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
        private readonly string $token,
    ) { }

    public function processPayment(
        float $amount,
        string $currency,
        string $cardNumber,
        string $cardExpMonth,
        string $cardExpYear,
        string $cardCvv,
        string $cardholderName
    ): TransactionDataTransfer {
        $aciPaymentEndpoint = 'https://eu-test.oppwa.com/v1/payments';

        $data = http_build_query([
            'entityId' => '8ac7a4c79394bdc801939736f17e063d',
            'amount' => $amount,
            'currency' => $currency,
            'paymentBrand' => 'VISA',
            'paymentType' => 'PA',
            'card.number' => $cardNumber,
            'card.holder' => $cardholderName,
            'card.expiryMonth' => $cardExpMonth,
            'card.expiryYear' => $cardExpYear,
            'card.cvv' => $cardCvv,
        ]);

        $response = $this->createRequest($aciPaymentEndpoint, $data);

        return new TransactionDataTransfer(
            $response['id'] ?? '',
                \DateTime::createFromFormat('Y-m-d H:i:s.uO', $response['timestamp'])->format('Y-m-d H:i:s') ?? '',                $response['amount'] ?? '',
                $response['currency'] ?? '',
                $response['card']['bin'] ?? ''
        );
    }

    private function createRequest(string $endpoint, string $data): array
    {
        try {
            $response = $this->client->request('POST', $endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => $data,
            ]);


            return json_decode($response->getContent(), true) ?? [];
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('ACI request failed', [
                'error' => $e->getMessage(),
                'endpoint' => $endpoint,
                'data' => $data,
            ]);
            throw new \RuntimeException('Failed to process ACI request', 0, $e);
        }
    }
}
