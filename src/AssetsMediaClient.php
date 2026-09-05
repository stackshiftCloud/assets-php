<?php

declare(strict_types=1);

namespace StackShift;

use Closure;

abstract class AssetsMediaClient
{
    public function __construct(
        private readonly Closure $transport,
        private readonly string $spaceId = '',
    ) {}

    protected function call(string $method, string $path, ?array $body = null, ?int $revision = null, ?string $idempotencyKey = null): array
    {
        $headers = [];
        if ($this->spaceId !== '') { $headers[] = 'X-Asset-Space-ID: ' . $this->spaceId; }
        if ($revision !== null) { $headers[] = 'If-Match: "' . $revision . '"'; }
        if ($idempotencyKey !== null) { $headers[] = 'Idempotency-Key: ' . $idempotencyKey; }
        return ($this->transport)($method, $path, $body, $headers);
    }
}
