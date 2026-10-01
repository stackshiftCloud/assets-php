<?php

declare(strict_types=1);

require __DIR__ . '/../src/AICredits.php';

final class CreditFixture
{
    use \StackShift\AICredits;

    public array $calls = [];

    public function request(string $method, string $path, array $body = []): array
    {
        $this->calls[] = [$method, $path, $body];
        return ['ok' => true];
    }
}

$client = new CreditFixture();
$client->aiCreditBalance();
$client->aiCreditUsage(['product' => 'agents']);
$client->quoteAICredits(1000, 'USD');
$client->checkoutAICredits('quote', 'retry-key', 'paystack');
$client->aiFundingModes();
$client->setAIFundingMode('assets', 'byok');

if (
    $client->calls[0][1] !== '/billing/ai-credits'
    || $client->calls[2][2] !== ['credits' => 1000, 'currency' => 'USD']
    || $client->calls[3][2]['idempotency_key'] !== 'retry-key'
    || $client->calls[4][0] !== 'GET'
    || $client->calls[5][0] !== 'PUT'
) {
    throw new RuntimeException('AI credit request contract failed');
}

echo "AI credit SDK requests passed\n";
