<?php

declare(strict_types=1);
namespace StackShift;

trait Partner
{
    public function partnerOverview(): array
    {
        return $this->request('GET', '/partner');
    }
    public function updatePartnerSettings(float $complaintPauseRate, float $bouncePauseRate): array
    {
        return $this->request('PUT', '/partner/settings', compact('complaintPauseRate', 'bouncePauseRate'));
    }
    public function purchasePartnerDomain(array $input, string $idempotencyKey): array
    {
        return $this->request('POST', '/partner/domains', $input, ['Idempotency-Key: ' . $idempotencyKey]);
    }
    public function listPartnerDomains(array $filters = []): array
    {
        $filters = array_map(static fn ($value) => is_bool($value) ? ($value ? 'true' : null) : $value, $filters);
        $query = http_build_query(array_filter($filters, static fn ($value) => $value !== null && $value !== ''));
        return $this->request('GET', '/partner/domains' . ($query === '' ? '' : '?' . $query));
    }
    public function getPartnerDomain(string $id): array
    {
        return $this->request('GET', '/partner/domains/' . rawurlencode($id));
    }
    public function retryPartnerDomain(string $id): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/retry');
    }
    public function getPartnerDiagnosis(string $id): array
    {
        return $this->request('GET', '/partner/domains/' . rawurlencode($id) . '/diagnosis');
    }
    public function restorePartnerDns(string $id): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/restore-dns');
    }
    public function transferOutPartnerDomain(string $id): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/transfer-out');
    }
    public function resumePartnerBrand(string $brandId): array
    {
        return $this->request('POST', '/partner/brands/' . rawurlencode($brandId) . '/resume');
    }
    public function getPartnerComplaints(string $brandId): array
    {
        return $this->request('GET', '/partner/brands/' . rawurlencode($brandId) . '/complaints');
    }
    public function listPartnerComplaintSources(string $brandId): array
    {
        return $this->request('GET', '/partner/brands/' . rawurlencode($brandId) . '/complaints/sources');
    }
    public function suppressPartnerNeverOpened(string $brandId, string $audienceId): array
    {
        return $this->request('POST', '/partner/brands/' . rawurlencode($brandId) . '/suppressions/bulk', ['audienceId' => $audienceId, 'reason' => 'never_opened']);
    }
    public function undoPartnerSuppression(string $brandId, string $bulkId): array
    {
        return $this->request('POST', '/partner/brands/' . rawurlencode($brandId) . '/suppressions/bulk/' . rawurlencode($bulkId) . '/undo');
    }
    public function listPartnerRequests(): array
    {
        return $this->request('GET', '/partner/requests');
    }
    public function createPartnerRequest(string $kind, ?string $message = null): array
    {
        return $this->request('POST', '/partner/requests', array_filter(['kind' => $kind, 'message' => $message], static fn ($value) => $value !== null));
    }
    public function partnerUsage(array $range = []): array
    {
        $query = http_build_query(array_filter($range, static fn ($value) => $value !== null && $value !== ''));
        return $this->request('GET', '/partner/usage' . ($query === '' ? '' : '?' . $query));
    }
}
