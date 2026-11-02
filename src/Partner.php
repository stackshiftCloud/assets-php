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
    public function connectPartnerDomain(array $input, string $idempotencyKey): array
    {
        return $this->request('POST', '/partner/domains/connect', $input, ['Idempotency-Key: ' . $idempotencyKey]);
    }
    public function transferInPartnerDomain(array $input, string $idempotencyKey): array
    {
        return $this->request('POST', '/partner/domains/transfer-in', $input, ['Idempotency-Key: ' . $idempotencyKey]);
    }
    public function transferInConnectedPartnerDomain(string $id, array $input, string $idempotencyKey): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/transfer-in', $input, ['Idempotency-Key: ' . $idempotencyKey]);
    }
    public function switchPartnerTransferNameservers(string $id): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/transfer-in/switch-nameservers');
    }
    public function setPartnerWebsite(string $id, string $projectId): array
    {
        return $this->request('PUT', '/partner/domains/' . rawurlencode($id) . '/website', ['projectId' => $projectId]);
    }
    public function removePartnerWebsite(string $id): array
    {
        return $this->request('DELETE', '/partner/domains/' . rawurlencode($id) . '/website');
    }
    public function checkPartnerWebsite(string $id): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/website/check');
    }
    public function listPartnerWebsiteProjects(): array
    {
        return $this->request('GET', '/partner/website-projects');
    }
    public function listPartnerMailboxes(string $id): array
    {
        return $this->request('GET', '/partner/domains/' . rawurlencode($id) . '/mailboxes');
    }
    public function createPartnerMailbox(string $id, array $input, string $idempotencyKey): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/mailboxes', $input, ['Idempotency-Key: ' . $idempotencyKey]);
    }
    public function updatePartnerMailbox(string $id, string $mailboxId, array $input): array
    {
        return $this->request('PATCH', '/partner/domains/' . rawurlencode($id) . '/mailboxes/' . rawurlencode($mailboxId), $input);
    }
    public function createPartnerMailboxPasswordLink(string $id, string $mailboxId, bool $reset, bool $send): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/mailboxes/' . rawurlencode($mailboxId) . '/password-link', compact('reset', 'send'));
    }
    public function pausePartnerMailbox(string $id, string $mailboxId): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/mailboxes/' . rawurlencode($mailboxId) . '/pause');
    }
    public function resumePartnerMailbox(string $id, string $mailboxId): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/mailboxes/' . rawurlencode($mailboxId) . '/resume');
    }
    public function removePartnerMailbox(string $id, string $mailboxId): array
    {
        return $this->request('DELETE', '/partner/domains/' . rawurlencode($id) . '/mailboxes/' . rawurlencode($mailboxId));
    }
    public function createPartnerAlias(string $id, string $localPart, string $deliverTo): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/aliases', compact('localPart', 'deliverTo'));
    }
    public function updatePartnerAlias(string $id, string $aliasId, string $deliverTo): array
    {
        return $this->request('PATCH', '/partner/domains/' . rawurlencode($id) . '/aliases/' . rawurlencode($aliasId), compact('deliverTo'));
    }
    public function removePartnerAlias(string $id, string $aliasId): void
    {
        $this->request('DELETE', '/partner/domains/' . rawurlencode($id) . '/aliases/' . rawurlencode($aliasId));
    }
    public function startPartnerMailMigration(string $id, array $input, string $idempotencyKey): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/mail-migration', $input, ['Idempotency-Key: ' . $idempotencyKey]);
    }
    public function getPartnerMailMigration(string $id): array
    {
        return $this->request('GET', '/partner/domains/' . rawurlencode($id) . '/mail-migration');
    }
    public function switchPartnerMailMigration(string $id, string $when): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/mail-migration/switch', compact('when'));
    }
    public function cancelPartnerMailMigration(string $id): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/mail-migration/cancel');
    }
    public function retryPartnerMailMigration(string $id): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/mail-migration/retry');
    }
    public function retryPartnerMailMigrationAccount(string $id, string $mailboxId, array $credentials = []): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/mail-migration/accounts/' . rawurlencode($mailboxId) . '/retry', $credentials === [] ? null : $credentials);
    }
    public function skipPartnerMailMigrationAccount(string $id, string $mailboxId): array
    {
        return $this->request('POST', '/partner/domains/' . rawurlencode($id) . '/mail-migration/accounts/' . rawurlencode($mailboxId) . '/skip');
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
