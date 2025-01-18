<?php

namespace App\Controller;

use App\DataTransfer\PaymentRequestDT;
use App\Integration\ACIIntegrationManager;
use App\Integration\Shift4IntegrationManager;
use App\Service\PaymentService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * PaymentController handles payment requests and integrates with payment providers.
 */
class PaymentController extends AbstractController
{
    /**
     * Constructor injects dependencies for payment providers, validation, and logging.
     */
    public function __construct(
        private readonly Shift4IntegrationManager $shift4IntegrationManager,
        private readonly ACIIntegrationManager $aciIntegrationManager,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger
    ) { }
/**
     * Handles a payment request for a specified provider.
     */
    #[Route('/app/example/{provider}', name: 'payment')]
    public function sendPaymentRequest(Request $request, string $provider): JsonResponse
    {
        // Extract and map the request payload into a data transfer object (DTO).

        $paymentData = new PaymentRequestDT($request->getPayload());
        // Validate the payment data using Symfony's Validator component.

        $errors = $this->validator->validate($paymentData);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        try {
            // Determine the integration manager based on the provided payment provider.

            $integrationManager = match ($provider) {
                'shift4' => $this->shift4IntegrationManager,
                'aci' => $this->aciIntegrationManager,
                default => throw new \RuntimeException('Invalid provider'),
            };

            $paymentService = new PaymentService($integrationManager);
            // Process the payment using the payment service.

            $response = $paymentService->processPayment(
                $paymentData->amount,
                $paymentData->currency,
                $paymentData->cardNumber,
                $paymentData->expMonth,
                $paymentData->expYear,
                $paymentData->cvv,
                $paymentData->cardholderName
            );

            return new JsonResponse($response->toArray());

        } catch (\RuntimeException $e) {
            $this->logger->error('Payment processing failed', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => 'Payment processing failed: ' . $e->getMessage()], 500);
        }
    }
}