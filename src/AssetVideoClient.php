<?php

declare(strict_types=1);

namespace StackShift;

final class AssetVideoClient extends AssetsMediaClient
{
    public function __construct(\Closure $transport, string $spaceId, private readonly \Closure $playback)
    {
        parent::__construct($transport, $spaceId);
    }

    public function workspace(string $assetId): array
    {
        return $this->call('GET', '/assets/' . rawurlencode($assetId) . '/video', null);
    }

    public function process(string $assetId, string $versionId): array
    {
        return $this->call('POST', '/assets/' . rawurlencode($assetId) . '/versions/' . rawurlencode($versionId) . '/video', null);
    }

    public function createSession(string $assetId): array
    {
        return $this->call('POST', '/assets/' . rawurlencode($assetId) . '/playback-sessions', null);
    }

    public function revokeSession(string $assetId, string $sessionId): array
    {
        return $this->call('DELETE', '/assets/' . rawurlencode($assetId) . '/playback-sessions/' . rawurlencode($sessionId), null);
    }

    public function captions(string $assetId, string $versionId): array
    {
        return $this->call('GET', '/assets/' . rawurlencode($assetId) . '/versions/' . rawurlencode($versionId) . '/video/captions', null);
    }

    public function addCaption(string $assetId, string $versionId, array $input): array
    {
        return $this->call('POST', '/assets/' . rawurlencode($assetId) . '/versions/' . rawurlencode($versionId) . '/video/captions', $input);
    }

    public function removeCaption(string $assetId, string $versionId, string $captionId): array
    {
        return $this->call('DELETE', '/assets/' . rawurlencode($assetId) . '/versions/' . rawurlencode($versionId) . '/video/captions/' . rawurlencode($captionId), null);
    }

    public function review(string $assetId, string $versionId, string $decision, string $reason): array
    {
        return $this->call('POST', '/assets/' . rawurlencode($assetId) . '/versions/' . rawurlencode($versionId) . '/video/review', ['decision' => $decision, 'reason' => $reason]);
    }

    public function analytics(string $assetId): array
    {
        return $this->call('GET', '/assets/' . rawurlencode($assetId) . '/video/analytics', null);
    }

    public function renewSession(string $credential): array
    {
        return ($this->playback)('/sessions/renew', $credential, []);
    }

    public function events(string $credential, array $events): array
    {
        return ($this->playback)('/events', $credential, ['events' => $events]);
    }
}
