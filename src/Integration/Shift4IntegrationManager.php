<?php

namespace App\Integration;

use App\DataTransfer\TransactionDataTransfer;
use App\Service\PaymentIntegrationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
/**
 * Shift4IntegrationManager handles payment processing using the Shift4 API.
 */
class Shift4IntegrationManager implements PaymentIntegrationInterface
{
    /**
     * Constructor to inject dependencies.
     *
     * @param HttpClientInterface $client The HTTP client to make API requests.
     * @param LoggerInterface $logger Logger to record errors and debug information.
     * @param string $apiKey The API key for authenticating Shift4 requests.
     */

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
        private readonly string $apiKey,
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
                // Endpoint to create a charge in Shift4.

        $shift4ChargeEndpoint = 'https://api.shift4.com/charges';
        // Generate a token for the card.

        $customerToken = $this->getToken($cardNumber, $cardExpMonth, $cardExpYear, $cardCvv, $cardholderName);
        // Create or retrieve a customer based on the token.

        $customer = $this->getCustomer($customerToken);
        // Payment token to be used in the charge.

        $paymentToken = $this->getToken($cardNumber, $cardExpMonth, $cardExpYear, $cardCvv, $cardholderName);
        // Request payload for processing the payment.

        $data = [
            'amount' =>$amount,
            'currency' => $currency,
            'customerId' => $customer,
            'card' => $paymentToken,
            'description' => 'Charge description'
        ];
        // Send the request and process the response.

        $response =  $this->createRequest($shift4ChargeEndpoint, $data);
        // Map the response to a TransactionDataTransfer object.

        return new TransactionDataTransfer(
            $response['id'] ?? '',
                \DateTime::createFromFormat('U', $response['created'])->format('Y-m-d H:i:s') ?? '',
            $response['amount'] ?? '',
            $response['currency'] ?? '',
            $response['card']['first6'] ?? ''
        );
    }

    public function getToken(
        string $cardNumber,
        string $cardExpMonth,
        string $cardExpYear,
        string $cardCvv,
        string $cardholderName
    ): string {
                // Endpoint to generate a card token.

        $shift4TokenEndpoint = 'https://api.shift4.com/tokens';

        $data = [
            'number' => $cardNumber,
            'expMonth' => $cardExpMonth,
            'expYear' => $cardExpYear,
            'cvc' => $cardCvv,
            'cardholderName' => $cardholderName,
        ];
        // Send the request and process the response.

        $response = $this->createRequest($shift4TokenEndpoint, $data);

        if (isset($response['id'])) {
            return $response['id'];
        }

        throw new \RuntimeException('Token generation failed');
    }

    public function getCustomer(string $token): string
    {
        // Endpoint to create or retrieve a customer.

        $shift4CustomerEndpoint = 'https://api.shift4.com/customers';

        $data = [
            'email' => 'adam@john.com',
            'card' => $token
        ];
        // Send the request and process the response.

        $response = $this->createRequest($shift4CustomerEndpoint, $data);

        if (isset($response['id'])) {
            return $response['id'];

        }

        throw new \RuntimeException('Customer creation failed');
    }

    private function createRequest(string $endpoint, array $data): array
    {
        try {
            // Send the POST request with authentication and JSON data.

            $response = $this->client->request('POST', $endpoint, [
                'auth_basic' => [$this->apiKey, ''],
                'json' => $data,
            ]);
            return json_decode($response->getContent(), true) ?? [];
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Shift4 request failed', [
                'error' => $e->getMessage(),
                'endpoint' => $endpoint,
                'data' => $data,
            ]);
            throw new \RuntimeException('Failed to process Shift4 request', 0, $e);
        }
    }
}
