<?php

declare(strict_types=1);

namespace StackShift;

trait AssetsPlatform
{
    public function listAssetConnections(): array { return $this->request('GET', '/assets/connections'); }
    public function getAssetConnection(string $id): array { return $this->request('GET', '/assets/connections/' . rawurlencode($id)); }
    public function createAssetConnection(array $input): array { return $this->request('POST', '/assets/connections', $input); }
    public function updateAssetConnection(string $id, array $input): array { return $this->request('PATCH', '/assets/connections/' . rawurlencode($id), $input); }
    public function verifyAssetConnection(string $id): array { return $this->request('POST', '/assets/connections/' . rawurlencode($id) . '/verify'); }
    public function deleteAssetConnection(string $id): array { return $this->request('DELETE', '/assets/connections/' . rawurlencode($id)); }

    public function configureAssetStorage(string $bucketId, int $revision, array $input): array
    {
        return $this->request('PUT', '/assets/buckets/' . rawurlencode($bucketId) . '/storage', $input,
            ['If-Match: "' . $revision . '"']);
    }

    public function listAssetS2Buckets(): array { return $this->request('GET', '/assets/s2-buckets'); }
    public function createAssetS2Bucket(array $input): array { return $this->request('POST', '/assets/s2-buckets', $input); }
    public function relocateAssetBucket(string $bucketId, array $input = []): array
    {
        return $this->request('POST', '/assets/buckets/' . rawurlencode($bucketId) . '/relocations', $input);
    }

    public function startAssetMigration(array $input): array { return $this->request('POST', '/assets/migrations', $input); }
    public function listAssetMigrations(?int $limit = null): array
    {
        return $this->request('GET', '/assets/migrations' . ($limit === null ? '' : '?limit=' . $limit));
    }
    public function getAssetMigration(string $id): array { return $this->request('GET', '/assets/migrations/' . rawurlencode($id)); }
    public function cancelAssetMigration(string $id): array { return $this->request('POST', '/assets/migrations/' . rawurlencode($id) . '/cancel'); }
    public function retryAssetMigrationItem(string $migrationId, string $itemId): array
    {
        return $this->request('POST', '/assets/migrations/' . rawurlencode($migrationId) . '/items/' . rawurlencode($itemId) . '/retry');
    }
    public function assetMigrationReport(string $id): array
    {
        return $this->request('GET', '/assets/migrations/' . rawurlencode($id) . '/report?format=json');
    }
}
