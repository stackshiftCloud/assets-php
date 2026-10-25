<?php

declare(strict_types=1);

require __DIR__ . '/../src/Partner.php';

final class PartnerFixture
{
    use \StackShift\Partner;

    public array $calls = [];

    public function request(string $method, string $path, ?array $body = null, array $headers = []): array
    {
        $this->calls[] = [$method, $path, $body, $headers];
        return ['ok' => true];
    }
}

$client = new PartnerFixture();
$purchase = [
    'domain' => 'merchantshop.com',
    'years' => 1,
    'currency' => 'USD',
    'merchantRef' => 'm_123',
    'registrant' => ['first_name' => 'Ada', 'last_name' => 'Obi', 'email' => 'ada@merchantshop.com', 'phone' => '+2348012345678', 'address' => '1 Marina', 'city' => 'Lagos', 'state' => 'Lagos', 'postal_code' => '101001', 'country' => 'NG'],
    'brand' => ['name' => 'Merchant Shop', 'postalAddress' => '1 Marina, Lagos'],
    'replies' => ['mode' => 'forward', 'forwardTo' => 'ada@gmail.com'],
];
$client->partnerOverview();
$client->updatePartnerSettings(0.002, 0.04);
$client->purchasePartnerDomain($purchase, 'order-1');
$client->listPartnerDomains(['merchantRef' => 'm_123', 'limit' => 20, 'offset' => null]);
$client->listPartnerDomains();
$client->getPartnerDomain('dom/1');
$client->retryPartnerDomain('dom_1');
$client->transferOutPartnerDomain('dom_1');
$client->resumePartnerBrand('brand_1');
$client->partnerUsage(['from' => '2026-10-01T00:00:00Z', 'to' => '2026-11-01T00:00:00Z']);
$client->listPartnerDomains(['q' => 'shop', 'attention' => true]);
$client->listPartnerDomains(['attention' => false]);
$client->getPartnerDiagnosis('dom/1');
$client->restorePartnerDns('dom_1');
$client->getPartnerComplaints('brand_1');
$client->listPartnerComplaintSources('brand_1');
$client->suppressPartnerNeverOpened('brand_1', 'aud_1');
$client->undoPartnerSuppression('brand_1', 'bulk/1');
$client->createPartnerRequest('purchase_limit', 'We onboard 100 merchants a day.');
$client->createPartnerRequest('dedicated_addresses');
$client->listPartnerRequests();

if (
    $client->calls[0] !== ['GET', '/partner', null, []]
    || $client->calls[1] !== ['PUT', '/partner/settings', ['complaintPauseRate' => 0.002, 'bouncePauseRate' => 0.04], []]
    || $client->calls[2] !== ['POST', '/partner/domains', $purchase, ['Idempotency-Key: order-1']]
    || $client->calls[3][1] !== '/partner/domains?merchantRef=m_123&limit=20'
    || $client->calls[4][1] !== '/partner/domains'
    || $client->calls[5][1] !== '/partner/domains/dom%2F1'
    || $client->calls[6][0] !== 'POST' || $client->calls[6][1] !== '/partner/domains/dom_1/retry'
    || $client->calls[7][1] !== '/partner/domains/dom_1/transfer-out'
    || $client->calls[8][0] !== 'POST' || $client->calls[8][1] !== '/partner/brands/brand_1/resume'
    || $client->calls[9][1] !== '/partner/usage?from=2026-10-01T00%3A00%3A00Z&to=2026-11-01T00%3A00%3A00Z'
    || $client->calls[10][1] !== '/partner/domains?q=shop&attention=true'
    || $client->calls[11][1] !== '/partner/domains'
    || $client->calls[12] !== ['GET', '/partner/domains/dom%2F1/diagnosis', null, []]
    || $client->calls[13][0] !== 'POST' || $client->calls[13][1] !== '/partner/domains/dom_1/restore-dns'
    || $client->calls[14] !== ['GET', '/partner/brands/brand_1/complaints', null, []]
    || $client->calls[15] !== ['GET', '/partner/brands/brand_1/complaints/sources', null, []]
    || $client->calls[16] !== ['POST', '/partner/brands/brand_1/suppressions/bulk', ['audienceId' => 'aud_1', 'reason' => 'never_opened'], []]
    || $client->calls[17][1] !== '/partner/brands/brand_1/suppressions/bulk/bulk%2F1/undo'
    || $client->calls[18] !== ['POST', '/partner/requests', ['kind' => 'purchase_limit', 'message' => 'We onboard 100 merchants a day.'], []]
    || $client->calls[19][2] !== ['kind' => 'dedicated_addresses']
    || $client->calls[20] !== ['GET', '/partner/requests', null, []]
) {
    throw new RuntimeException('Partner request contract failed');
}

echo "Partner SDK requests passed\n";
