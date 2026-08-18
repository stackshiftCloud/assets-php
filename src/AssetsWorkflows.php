<?php

declare(strict_types=1);

namespace StackShift;

trait AssetsWorkflows
{
    public function createTransformationV2(string $name, array $definition, bool $eager = false): array
    {
        return $this->request('POST', '/assets/transformations', [
            'name' => $name, 'definition' => $definition, 'eager' => $eager,
        ]);
    }

    public function materializeTransform(string $assetId, array $input, ?string $idempotencyKey = null): array
    {
        $headers = $idempotencyKey ? ['Idempotency-Key: ' . $idempotencyKey] : [];
        return $this->request('POST', '/assets/' . rawurlencode($assetId) . '/transforms', $input, $headers);
    }

    public function derivatives(string $assetId): array
    {
        return $this->request('GET', '/assets/' . rawurlencode($assetId) . '/derivatives');
    }

    public function generate(array $input, string $idempotencyKey): array
    {
        return $this->request('POST', '/assets/generations', $input, ['Idempotency-Key: ' . $idempotencyKey]);
    }

    public function automation(): array
    {
        return $this->request('GET', '/assets/automation');
    }

    public function workflowCatalog(): array
    {
        return $this->request('GET', '/assets/workflows/catalog');
    }

    public function createWorkflow(array $input): array
    {
        return $this->request('POST', '/assets/workflows', $input);
    }

    public function workflows(int $page = 1, int $perPage = 20): array
    {
        return $this->request('GET', '/assets/workflows?' . http_build_query(['page' => $page, 'per_page' => $perPage]));
    }

    public function workflow(string $workflowId): array
    {
        return $this->request('GET', $this->workflowPath($workflowId));
    }

    public function updateWorkflowDraft(string $workflowId, array $input): array
    {
        return $this->request('PUT', $this->workflowPath($workflowId) . '/draft', $input);
    }

    public function validateWorkflow(string $workflowId): array
    {
        return $this->request('POST', $this->workflowPath($workflowId) . '/validate');
    }

    public function publishWorkflow(string $workflowId, int $expectedRevision): array
    {
        return $this->request('POST', $this->workflowPath($workflowId) . '/publish', [
            'expected_revision' => $expectedRevision,
        ]);
    }

    public function activateWorkflow(string $workflowId, string $versionId, int $expectedRevision): void
    {
        $this->request('POST', $this->workflowPath($workflowId) . '/activate', [
            'version_id' => $versionId, 'expected_revision' => $expectedRevision,
        ]);
    }

    public function workflowVersions(string $workflowId): array
    {
        return $this->request('GET', $this->workflowPath($workflowId) . '/versions');
    }

    public function runWorkflow(string $workflowId, array $input = [], ?string $idempotencyKey = null): array
    {
        $headers = $idempotencyKey ? ['Idempotency-Key: ' . $idempotencyKey] : [];
        return $this->request('POST', $this->workflowPath($workflowId) . '/run', $input, $headers);
    }

    public function workflowRuns(string $workflowId, int $page = 1, int $perPage = 20): array
    {
        $query = http_build_query(['page' => $page, 'per_page' => $perPage]);
        return $this->request('GET', $this->workflowPath($workflowId) . '/runs?' . $query);
    }

    public function workflowRun(string $runId): array
    {
        return $this->request('GET', $this->workflowRunPath($runId));
    }

    public function cancelWorkflowRun(string $runId): array
    {
        return $this->request('POST', $this->workflowRunPath($runId) . '/cancel');
    }

    public function retryWorkflowRun(string $runId): array
    {
        return $this->request('POST', $this->workflowRunPath($runId) . '/retry');
    }

    public function workflowApprovals(?string $status = null): array
    {
        $suffix = $status ? '?' . http_build_query(['status' => $status]) : '';
        return $this->request('GET', '/assets/workflows/approvals' . $suffix);
    }

    public function decideWorkflowApproval(string $approvalId, bool $approved, array $input = []): array
    {
        return $approved ? $this->approveWorkflow($approvalId, $input) : $this->rejectWorkflow($approvalId, $input);
    }

    public function approveWorkflow(string $approvalId, array $input = []): array
    {
        return $this->request('POST', '/assets/workflows/approvals/' . rawurlencode($approvalId) . '/approve', $input);
    }

    public function rejectWorkflow(string $approvalId, array $input = []): array
    {
        return $this->request('POST', '/assets/workflows/approvals/' . rawurlencode($approvalId) . '/reject', $input);
    }

    public function workflowConnections(): array
    {
        return $this->request('GET', '/assets/workflows/connections');
    }

    public function createWorkflowConnection(array $input): array
    {
        return $this->request('POST', '/assets/workflows/connections', $input);
    }

    public function rotateWorkflowConnectionCredential(string $connectionId, array $input): array
    {
        return $this->request('POST', '/assets/workflows/connections/' . rawurlencode($connectionId) . '/credential/rotate', $input);
    }

    public function rotateWorkflowInboundHook(string $workflowId): array
    {
        return $this->request('POST', $this->workflowPath($workflowId) . '/inbound-hook/rotate');
    }

    private function workflowPath(string $workflowId): string
    {
        return '/assets/workflows/' . rawurlencode($workflowId);
    }

    private function workflowRunPath(string $runId): string
    {
        return '/assets/workflows/runs/' . rawurlencode($runId);
    }
}
