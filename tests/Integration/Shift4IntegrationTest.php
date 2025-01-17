<?php

namespace App\Tests\Integration;

use App\Integration\Shift4IntegrationManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class Shift4IntegrationTest extends KernelTestCase
{
    private Shift4IntegrationManager $Shift4IntegrationManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->Shift4IntegrationManager = $container->get(Shift4IntegrationManager::class);
    }

    public function testProcessPaymentIntegration()
    {
        $transactionDTO = $this->Shift4IntegrationManager->processPayment(
            499,
            'USD',
            '4242424242424242',
            '11',
            '2027', '123',
            'John Doe'
        );

        $this->assertNotNull($transactionDTO->getTransactionId());
        $this->assertEquals(499, $transactionDTO->getAmount());
        $this->assertEquals('USD', $transactionDTO->getCurrency());
    }
}
