<?php

namespace App\Controller;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class Shift4Controller
{
    private HttpClientInterface $httpClient;

    // Inject HttpClientInterface automatically
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/shift4/charge', name: 'shift4_charge', methods: ['POST'])]
    public function charge(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $response = $this->httpClient->request('POST', 'https://api.shift4.com/charges', [
                'headers' => [
                    'Authorization' => 'Bearer sk_test_9SKAbYZyru17vnPny1mqmy23', // Replace with your actual API key
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            $responseData = $response->toArray(); // Converts the response to an array

            return new JsonResponse($responseData);

        } catch (\Symfony\Component\HttpClient\Exception\ClientException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

       
    }
}