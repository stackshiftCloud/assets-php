<?php

declare(strict_types=1);

namespace StackShift;

use RuntimeException;

trait AssetsMedia
{
    public function dam(string $spaceId = ''): AssetDAMClient
    {
        return new AssetDAMClient($this->request(...), $spaceId);
    }

    public function video(string $spaceId = ''): AssetVideoClient
    {
        return new AssetVideoClient($this->request(...), $spaceId, function (string $path, string $credential, array $body): array {
            if (trim($credential) === '') { throw new RuntimeException('Playback credential is required'); }
            $base = parse_url($this->baseUrl);
            if (!isset($base['scheme'], $base['host'])) { throw new RuntimeException('Invalid Assets base URL'); }
            $origin = $base['scheme'] . '://' . $base['host'] . (isset($base['port']) ? ':' . $base['port'] : '');
            return $this->curlAbsolute('POST', $origin . '/playback' . $path,
                json_encode($body === [] ? (object) [] : $body, JSON_THROW_ON_ERROR),
                ['Content-Type: application/json', 'Authorization: Bearer ' . $credential]);
        });
    }
}
