<?php

declare(strict_types=1);

namespace StackShift;

use RuntimeException;

final class AssetsClient
{
    use AssetsWorkflows;
    use AssetsMedia;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://api.stackshift.cloud/api/v1',
        private readonly string $cdnBaseUrl = 'https://cdn.stackshift.cloud',
    ) {
        if (trim($apiKey) === '') {
            throw new RuntimeException('StackShift SDK requires an API key');
        }
    }

    public function list(array $filters = []): array
    {
        $query = http_build_query(array_filter($filters, static fn ($v) => $v !== null && $v !== ''));
        return $this->request('GET', '/assets' . ($query === '' ? '' : '?' . $query));
    }

    public function get(string $assetId): array
    {
        return $this->request('GET', '/assets/' . rawurlencode($assetId));
    }

    public function delete(string $assetId, int $revision, ?string $idempotencyKey = null): array
    {
		$headers = ['If-Match: "' . $revision . '"'];
		if ($idempotencyKey !== null && $idempotencyKey !== '') {
			$headers[] = 'Idempotency-Key: ' . $idempotencyKey;
		}
		return $this->request('DELETE', '/assets/' . rawurlencode($assetId), null, $headers);
    }

    public function upload(string $filePath, array $options = []): array
    {
        return $this->multipart('POST', '/assets/upload', $filePath, $options);
    }

    public function replace(string $assetId, int $revision, string $filePath, array $options = []): array
    {
        return $this->multipart('PUT', '/assets/' . rawurlencode($assetId) . '/replace', $filePath, $options, ['If-Match: "' . $revision . '"']);
    }

    public function createUploadSession(array $options, ?string $idempotencyKey = null): array
    {
        $headers = $idempotencyKey ? ['Idempotency-Key: ' . $idempotencyKey] : [];
        return $this->request('POST', '/assets/upload-sessions', $this->clean($options), $headers);
    }

    public function signedUploadUrl(array $options, ?string $idempotencyKey = null): array
    {
        $headers = $idempotencyKey ? ['Idempotency-Key: ' . $idempotencyKey] : [];
        return $this->request('POST', '/assets/upload-url', $this->clean($options), $headers);
    }

    public function uploadChunk(string $uploadUrlOrToken, int $partNumber, string $bytes): array
    {
        $target = str_starts_with($uploadUrlOrToken, 'http')
            ? rtrim($uploadUrlOrToken, '/')
            : preg_replace('~/api/v1$~', '', rtrim($this->baseUrl, '/')) . '/uploads/' . rawurlencode($uploadUrlOrToken);
		return $this->curlAbsolute('PUT', $target . '/parts/' . $partNumber, $bytes, ['Content-Type: application/octet-stream', 'X-Content-SHA256: ' . hash('sha256', $bytes)]);
    }

    public function completeUploadSession(string $sessionId): array
    {
        return $this->request('POST', '/assets/upload-sessions/' . rawurlencode($sessionId) . '/complete');
    }

    public function resumeUploadSession(string $sessionId): array
    {
        return $this->request('GET', '/assets/upload-sessions/' . rawurlencode($sessionId));
    }

    public function cancelUploadSession(string $sessionId): array
    {
        return $this->request('DELETE', '/assets/upload-sessions/' . rawurlencode($sessionId));
    }

    public function signedUrl(string $assetId, string $expiresIn = '10m', ?int $maxDownloads = null): array
    {
        return $this->request('POST', '/assets/' . rawurlencode($assetId) . '/signed-url', $this->clean([
			'expires_in' => $expiresIn, 'max_downloads' => $maxDownloads,
        ]));
    }

    public function listBuckets(): array { return $this->request('GET', '/assets/buckets'); }
    public function createBucket(array $policy): array { return $this->request('POST', '/assets/buckets', $policy); }

    public function updateBucket(string $bucketId, int $revision, array $policy): array
    {
        return $this->request('PUT', '/assets/buckets/' . rawurlencode($bucketId), $policy, ['If-Match: "' . $revision . '"']);
    }

    public function deleteBucket(string $bucketId, int $revision): void
    {
        $this->request('DELETE', '/assets/buckets/' . rawurlencode($bucketId), null, ['If-Match: "' . $revision . '"']);
    }

    public function patch(string $assetId, int $revision, array $changes): array
    {
        return $this->request('PATCH', '/assets/' . rawurlencode($assetId), $changes, ['If-Match: "' . $revision . '"']);
    }

    public function setTags(string $assetId, int $revision, array $tags): array
    {
        return $this->request('PUT', '/assets/' . rawurlencode($assetId) . '/tags', ['tags' => $tags], ['If-Match: "' . $revision . '"']);
    }

    public function patchMetadata(string $assetId, int $revision, array $metadata): array
    {
        return $this->request('PATCH', '/assets/' . rawurlencode($assetId) . '/metadata', ['metadata' => $metadata], ['If-Match: "' . $revision . '"']);
    }

	public function getAIConfig(): array { return $this->request('GET', '/assets/ai/config'); }
	public function getPolicy(): array { return $this->request('GET', '/assets/policy'); }
	public function updatePolicy(array $policy): array { return $this->request('PUT', '/assets/policy', $policy); }
	public function listTransformations(): array { return $this->request('GET', '/assets/transformations'); }
	public function createTransformation(string $name, array $options): array
	{
		return $this->request('POST', '/assets/transformations', array_merge($options, ['name' => $name]));
	}
	public function deleteTransformation(string $name): void { $this->request('DELETE', '/assets/transformations/' . rawurlencode($name)); }
	public function signedTransformUrl(string $assetId, array $options): array
	{
		return $this->request('POST', '/assets/' . rawurlencode($assetId) . '/transform-url', $options);
	}

	public function updateAIConfig(int $revision, array $enabledActions, int $monthlySpendCapMicros): array
	{
		return $this->request('PUT', '/assets/ai/config', [
			'enabled_actions' => $enabledActions, 'monthly_spend_cap_micros' => $monthlySpendCapMicros,
		], ['If-Match: "' . $revision . '"']);
	}

	public function job(string $jobId): array { return $this->request('GET', '/assets/jobs/' . rawurlencode($jobId)); }
	public function cancelJob(string $jobId): array { return $this->request('POST', '/assets/jobs/' . rawurlencode($jobId) . '/cancel'); }
	public function analyze(string $assetId, ?string $idempotencyKey = null): array { return $this->assetJob('/assets/' . rawurlencode($assetId) . '/ai/analyze', $idempotencyKey); }
	public function ai(string $assetId): array { return $this->request('GET', '/assets/' . rawurlencode($assetId) . '/ai'); }
	public function moderate(string $assetId, ?string $idempotencyKey = null): array { return $this->assetJob('/assets/' . rawurlencode($assetId) . '/moderation', $idempotencyKey); }
	public function transcript(string $assetId): array { return $this->request('GET', '/assets/' . rawurlencode($assetId) . '/transcript'); }
	public function requestTranscript(string $assetId, ?string $idempotencyKey = null): array { return $this->assetJob('/assets/' . rawurlencode($assetId) . '/transcript', $idempotencyKey); }
	public function requestSmartCrop(string $assetId, ?string $idempotencyKey = null): array { return $this->assetJob('/assets/' . rawurlencode($assetId) . '/smart-crop', $idempotencyKey); }
	public function requestBackgroundRemoval(string $assetId, ?string $idempotencyKey = null): array { return $this->assetJob('/assets/' . rawurlencode($assetId) . '/background-remove', $idempotencyKey); }
	public function createLifecycleRule(array $rule): array { return $this->request('POST', '/assets/lifecycle-rules', $rule); }
	public function listLifecycleRules(): array { return $this->request('GET', '/assets/lifecycle-rules'); }
	public function deleteLifecycleRule(string $ruleId): void { $this->request('DELETE', '/assets/lifecycle-rules/' . rawurlencode($ruleId)); }
	public function createDomain(string $domain): array { return $this->request('POST', '/assets/domains', ['domain' => $domain]); }
	public function listDomains(): array { return $this->request('GET', '/assets/domains'); }
	public function verifyDomain(string $domainId): array { return $this->request('POST', '/assets/domains/' . rawurlencode($domainId) . '/verify'); }
	public function deleteDomain(string $domainId): void { $this->request('DELETE', '/assets/domains/' . rawurlencode($domainId)); }
	public function analytics(): array { return $this->request('GET', '/assets/analytics'); }
	public function usageSummary(): array { return $this->request('GET', '/assets/summary'); }
	public function events(int $limit = 50): array { return $this->request('GET', '/assets/events?limit=' . $limit); }
	public function createWebhook(string $url, array $eventTypes = ['asset.*']): array { return $this->request('POST', '/assets/webhooks', ['url' => $url, 'event_types' => $eventTypes]); }
	public function listWebhooks(): array { return $this->request('GET', '/assets/webhooks'); }
	public function deleteWebhook(string $webhookId): array { return $this->request('DELETE', '/assets/webhooks/' . rawurlencode($webhookId)); }
	public function listWebhookDeliveries(string $webhookId, int $limit = 50): array { return $this->request('GET', '/assets/webhooks/' . rawurlencode($webhookId) . '/deliveries?limit=' . $limit); }
	public function retryWebhookDelivery(string $deliveryId): array { return $this->request('POST', '/assets/webhook-deliveries/' . rawurlencode($deliveryId) . '/retry'); }
	public function purge(string $assetId): array { return $this->request('POST', '/assets/' . rawurlencode($assetId) . '/purge'); }
	public function bulk(string $action, array $items, array $options = [], ?string $idempotencyKey = null): array
	{
		$headers = $idempotencyKey ? ['Idempotency-Key: ' . $idempotencyKey] : [];
		return $this->request('POST', '/assets/bulk', array_merge($options, ['action' => $action, 'items' => $items]), $headers);
	}
	public function versions(string $assetId): array { return $this->request('GET', '/assets/' . rawurlencode($assetId) . '/versions'); }
	public function createBranch(string $assetId, string $name, ?string $fromVersionId = null): array
	{
		return $this->request('POST', '/assets/' . rawurlencode($assetId) . '/branches', $this->clean(['name' => $name, 'from_version_id' => $fromVersionId]));
	}
	public function restoreVersion(string $assetId, string $versionId): array
	{
		return $this->request('POST', '/assets/' . rawurlencode($assetId) . '/versions/' . rawurlencode($versionId) . '/restore');
	}
	public function promoteBranch(string $assetId, string $branch): array
	{
		return $this->request('POST', '/assets/' . rawurlencode($assetId) . '/branches/' . rawurlencode($branch) . '/promote');
	}
	public function collections(): array { return $this->request('GET', '/assets/collections'); }
	public function createCollection(array $collection): array { return $this->request('POST', '/assets/collections', $collection); }
	public function updateCollection(string $collectionId, array $collection): array { return $this->request('PUT', '/assets/collections/' . rawurlencode($collectionId), $collection); }
	public function deleteCollection(string $collectionId): void { $this->request('DELETE', '/assets/collections/' . rawurlencode($collectionId)); }
	public function savedSearches(): array { return $this->request('GET', '/assets/saved-searches'); }
	public function createSavedSearch(array $search): array { return $this->request('POST', '/assets/saved-searches', $search); }
	public function updateSavedSearch(string $searchId, array $search): array { return $this->request('PUT', '/assets/saved-searches/' . rawurlencode($searchId), $search); }
	public function deleteSavedSearch(string $searchId): void { $this->request('DELETE', '/assets/saved-searches/' . rawurlencode($searchId)); }

    public function immutableVersionUrl(string $assetId, string $versionId, string $filename = 'asset'): string
    {
        return rtrim($this->cdnBaseUrl, '/') . '/assets/' . rawurlencode($assetId) . '/versions/' . rawurlencode($versionId) . '/' . rawurlencode($filename);
    }

	public function versionUrl(string $assetId, string $versionId, ?string $token = null): string
	{
		$prefix = $token ? '/private/assets' : '/assets';
		$url = rtrim($this->cdnBaseUrl, '/') . $prefix . '/' . rawurlencode($assetId) . '/versions/' . rawurlencode($versionId);
		return $token ? $url . '?token=' . rawurlencode($token) : $url;
	}

	public function branchUrl(string $assetId, string $branch, ?string $token = null): string
	{
		$prefix = $token ? '/private/assets' : '/assets';
		$url = rtrim($this->cdnBaseUrl, '/') . $prefix . '/' . rawurlencode($assetId) . '/branches/' . rawurlencode($branch);
		return $token ? $url . '?token=' . rawurlencode($token) : $url;
	}

	public function videoUrl(string $assetId, string $kind, string $profile = 'default', ?string $token = null): string
	{
		$prefix = $token ? '/private/assets' : '/assets';
		$url = rtrim($this->cdnBaseUrl, '/') . $prefix . '/' . rawurlencode($assetId) . '/video/' . rawurlencode($kind) . '/' . rawurlencode($profile);
		return $token ? $url . '?token=' . rawurlencode($token) : $url;
	}

    private function multipart(string $method, string $path, string $filePath, array $fields, array $headers = []): array
    {
        if (!is_file($filePath)) {
            throw new RuntimeException('Asset upload path is not a file');
        }
        $fields = $this->clean($fields);
        $fields['file'] = new \CURLFile($filePath);
        return $this->curl($method, $path, $fields, $headers);
    }

	private function assetJob(string $path, ?string $idempotencyKey): array
	{
		$headers = $idempotencyKey ? ['Idempotency-Key: ' . $idempotencyKey] : [];
		return $this->request('POST', $path, null, $headers);
	}

    private function request(string $method, string $path, ?array $body = null, array $headers = []): mixed
    {
        $headers[] = 'Content-Type: application/json';
        return $this->curl($method, $path, $body === null ? null : json_encode($body, JSON_THROW_ON_ERROR), $headers);
    }

    private function curl(string $method, string $path, mixed $body, array $headers): mixed
    {
        return $this->curlAbsolute($method, rtrim($this->baseUrl, '/') . $path, $body, $headers, true);
    }

    private function curlAbsolute(string $method, string $url, mixed $body, array $headers = [], bool $authenticated = false): mixed
    {
        $handle = curl_init($url);
        if ($authenticated) {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }
        curl_setopt_array($handle, [
            CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body, CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true,
        ]);
        $raw = curl_exec($handle);
        if ($raw === false) {
            throw new RuntimeException(curl_error($handle));
        }
        $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $headerSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $responseBody = substr($raw, $headerSize);
        curl_close($handle);
        $decoded = $responseBody === '' ? null : json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException($decoded['error']['message'] ?? $decoded['message'] ?? "StackShift API request failed with {$status}");
        }
        return is_array($decoded) && array_key_exists('data', $decoded) ? $decoded['data'] : $decoded;
    }

    private function clean(array $values): array
    {
        return array_filter($values, static fn ($value) => $value !== null && $value !== '');
    }
}
