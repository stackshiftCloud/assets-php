<?php

declare(strict_types=1);

namespace StackShift;

final class AssetDAMClient extends AssetsMediaClient
{
    public function spaces(): array
    {
        return $this->call('GET', '/assets/spaces', null);
    }

    public function collaborators(): array
    {
        return $this->call('GET', '/assets/collaborators', null);
    }

    public function invite(array $input): array
    {
        return $this->call('POST', '/assets/collaborators', $input);
    }

    public function acceptInvitation(string $id): array
    {
        return $this->call('POST', '/assets/invitations/' . rawurlencode($id) . '/accept', null);
    }

    public function updateCollaborator(string $id, int $revision, array $actions): array
    {
        return $this->call('PUT', '/assets/collaborators/' . rawurlencode($id), ['actions' => $actions], $revision);
    }

    public function revokeCollaborator(string $id, int $revision): array
    {
        return $this->call('DELETE', '/assets/collaborators/' . rawurlencode($id), null, $revision);
    }

    public function metadataSchemas(): array
    {
        return $this->call('GET', '/assets/metadata-schemas', null);
    }

    public function metadataEditor(string $assetId): array
    {
        return $this->call('GET', '/assets/' . rawurlencode($assetId) . '/metadata-editor', null);
    }

    public function createMetadataSchema(array $input): array
    {
        return $this->call('POST', '/assets/metadata-schemas', $input);
    }

    public function updateMetadataSchema(string $id, int $revision, array $input): array
    {
        return $this->call('PUT', '/assets/metadata-schemas/' . rawurlencode($id), $input, $revision);
    }

    public function publishMetadataSchema(string $id, int $revision): array
    {
        return $this->call('POST', '/assets/metadata-schemas/' . rawurlencode($id) . '/publish', null, $revision);
    }

    public function bucketGovernance(string $id): array
    {
        return $this->call('GET', '/assets/buckets/' . rawurlencode($id) . '/governance', null);
    }

    public function saveBucketGovernance(string $id, array $input): array
    {
        return $this->call('PUT', '/assets/buckets/' . rawurlencode($id) . '/governance', $input);
    }

    public function migrateBucketGovernance(string $id, int $revision): array
    {
        return $this->call('POST', '/assets/buckets/' . rawurlencode($id) . '/governance/migrate', null, $revision);
    }

    public function publications(array $query = []): array
    {
        return $this->call('GET', '/assets/publications' . ($query === [] ? '' : '?' . http_build_query($query)), null);
    }

    public function publicationRenditions(string $assetId): array
    {
        return $this->call('GET', '/assets/' . rawurlencode($assetId) . '/publication-renditions', null);
    }

    public function createPublication(array $input): array
    {
        return $this->call('POST', '/assets/publications', $input);
    }

    public function updatePublication(string $id, int $revision, array $input): array
    {
        return $this->call('PUT', '/assets/publications/' . rawurlencode($id), $input, $revision);
    }

    public function transitionPublication(string $id, int $revision, string $action, string $note): array
    {
        return $this->call('POST', '/assets/publications/' . rawurlencode($id) . '/' . rawurlencode($action), ['note' => $note], $revision);
    }

    public function resolvePublication(string $id): array
    {
        return $this->call('GET', '/assets/publications/' . rawurlencode($id) . '/resolve', null);
    }

    public function previewPublication(string $id): array
    {
        return $this->call('GET', '/assets/publications/' . rawurlencode($id) . '/preview', null);
    }

    public function resolvePublicationAR(string $id): array
    {
        return $this->call('POST', '/assets/publications/' . rawurlencode($id) . '/ar', null);
    }

    public function createPickerCapability(array $input): array
    {
        return $this->call('POST', '/assets/picker-capabilities', $input);
    }

    public function revokePickerCapability(string $id): array
    {
        return $this->call('DELETE', '/assets/picker-capabilities/' . rawurlencode($id), null);
    }

    public function models(string $assetId): array
    {
        return $this->call('GET', '/assets/' . rawurlencode($assetId) . '/models', null);
    }

    public function estimateRender(string $assetId, int $revision, array $input): array
    {
        return $this->call('POST', '/assets/' . rawurlencode($assetId) . '/models', array_merge($input, ['operation' => 'estimate']), $revision);
    }

    public function renderProduct(string $assetId, int $revision, string $idempotencyKey, array $input, int $maximumUnits): array
    {
        return $this->call('POST', '/assets/' . rawurlencode($assetId) . '/models', array_merge($input, ['operation' => 'render', 'maximum_units' => $maximumUnits]), $revision, $idempotencyKey);
    }

    public function processModel(string $assetId, int $revision, string $idempotencyKey): array
    {
        return $this->call('POST', '/assets/' . rawurlencode($assetId) . '/models', null, $revision, $idempotencyKey);
    }

    public function galleries(array $query = []): array
    {
        return $this->call('GET', '/assets/galleries' . ($query === [] ? '' : '?' . http_build_query($query)), null);
    }

    public function createGallery(array $input): array
    {
        return $this->call('POST', '/assets/galleries', $input);
    }

    public function updateGallery(string $id, int $revision, array $input): array
    {
        return $this->call('PUT', '/assets/galleries/' . rawurlencode($id), $input, $revision);
    }

    public function transitionGallery(string $id, int $revision, string $action): array
    {
        return $this->call('POST', '/assets/galleries/' . rawurlencode($id) . '/' . rawurlencode($action), null, $revision);
    }

    public function resolveGallery(string $id): array
    {
        return $this->call('GET', '/assets/galleries/' . rawurlencode($id) . '/resolve', null);
    }

    public function previewGallery(string $id): array
    {
        return $this->call('GET', '/assets/galleries/' . rawurlencode($id) . '/resolve?preview=true', null);
    }

    public function resolveGalleryAR(string $id, string $publicationId): array
    {
        return $this->call('POST', '/assets/galleries/' . rawurlencode($id) . '/ar', ['publication_id' => $publicationId]);
    }

}
