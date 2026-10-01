<?php

declare(strict_types=1);
namespace StackShift;

trait AICredits
{
    public function aiCreditBalance(): array
    {
        return $this->request('GET', '/billing/ai-credits');
    }
    public function aiCreditUsage(array $filters = []): array
    {
        return $this->request('GET', '/billing/ai-credits/usage?' . http_build_query($filters));
    }
    public function quoteAICredits(int $credits, string $currency): array
    {
        return $this->request('POST', '/billing/ai-credits/top-ups/quote', compact('credits', 'currency'));
    }
    public function checkoutAICredits(string $quoteId, string $idempotencyKey, string $provider): array
    {
        return $this->request('POST', '/billing/ai-credits/top-ups/checkout', [
            'quote_id' => $quoteId, 'idempotency_key' => $idempotencyKey, 'provider' => $provider,
        ]);
    }
    public function aiFundingModes(): array
    {
        return $this->request('GET', '/billing/ai-credits/funding');
    }
    public function setAIFundingMode(string $product, string $mode): array
    {
        return $this->request('PUT', '/billing/ai-credits/funding', compact('product', 'mode'));
    }
}
