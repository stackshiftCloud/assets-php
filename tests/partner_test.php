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

$growth = new PartnerFixture();
$brand = ['name' => 'Kicks by Femi', 'postalAddress' => '1 Marina, Lagos'];
$replies = ['mode' => 'mailbox', 'mailboxRecoveryEmail' => 'femi@gmail.com', 'sendPasswordLink' => false];
$connect = ['domain' => 'kicksbyfemi.ng', 'merchantRef' => 'm_1', 'brand' => $brand, 'replies' => $replies, 'website' => ['projectId' => 'prj_1'], 'dns' => ['method' => 'cloudflare', 'cloudflareToken' => 'cf_token']];
$growth->connectPartnerDomain($connect, 'connect-1');
$growth->transferInPartnerDomain($connect + ['authCode' => 'Zx9-auth'], 'transfer-1');
$growth->transferInConnectedPartnerDomain('dom/1', ['authCode' => 'Zx9-auth'], 'transfer-2');
$growth->setPartnerWebsite('dom_1', 'prj_2');
$growth->removePartnerWebsite('dom_1');
$growth->checkPartnerWebsite('dom_1');
$growth->listPartnerWebsiteProjects();
$growth->listPartnerDomains(['website' => 'pending']);
$growth->listPartnerMailboxes('dom_1');
$growth->createPartnerMailbox('dom_1', ['localPart' => 'hello', 'sendPasswordLink' => true], 'mailbox-1');
$growth->updatePartnerMailbox('dom_1', 'mbx/1', ['displayName' => 'Femi A.']);
$growth->createPartnerMailboxPasswordLink('dom_1', 'mbx_1', true, false);
$growth->pausePartnerMailbox('dom_1', 'mbx_1');
$growth->resumePartnerMailbox('dom_1', 'mbx_1');
$growth->removePartnerMailbox('dom_1', 'mbx_1');
$growth->createPartnerAlias('dom_1', 'orders', 'hello@kicksbyfemi.ng');
$growth->updatePartnerAlias('dom_1', 'als_1', 'sales@kicksbyfemi.ng');
$growth->removePartnerAlias('dom_1', 'als_1');
$accounts = [['mailboxId' => 'mbx_1', 'username' => 'femi@kicksbyfemi.ng', 'password' => 'app-password']];
$growth->startPartnerMailMigration('dom_1', ['source' => 'google', 'accounts' => $accounts, 'switch' => 'tonight'], 'migration-1');
$growth->getPartnerMailMigration('dom_1');
$growth->switchPartnerMailMigration('dom_1', 'now');
$growth->cancelPartnerMailMigration('dom_1');
$growth->skipPartnerMailMigrationAccount('dom_1', 'mbx/1');
$growth->retryPartnerMailMigration('dom_1');
$growth->retryPartnerMailMigrationAccount('dom_1', 'mbx/1', ['username' => 'femi@kicksbyfemi.ng', 'password' => 'new-app-password']);
$growth->retryPartnerMailMigrationAccount('dom_1', 'mbx_1');
$growth->switchPartnerTransferNameservers('dom/1');

if (
    $growth->calls[0] !== ['POST', '/partner/domains/connect', $connect, ['Idempotency-Key: connect-1']]
    || $growth->calls[1] !== ['POST', '/partner/domains/transfer-in', $connect + ['authCode' => 'Zx9-auth'], ['Idempotency-Key: transfer-1']]
    || $growth->calls[2] !== ['POST', '/partner/domains/dom%2F1/transfer-in', ['authCode' => 'Zx9-auth'], ['Idempotency-Key: transfer-2']]
    || $growth->calls[3] !== ['PUT', '/partner/domains/dom_1/website', ['projectId' => 'prj_2'], []]
    || $growth->calls[4] !== ['DELETE', '/partner/domains/dom_1/website', null, []]
    || $growth->calls[5] !== ['POST', '/partner/domains/dom_1/website/check', null, []]
    || $growth->calls[6] !== ['GET', '/partner/website-projects', null, []]
    || $growth->calls[7][1] !== '/partner/domains?website=pending'
    || $growth->calls[8] !== ['GET', '/partner/domains/dom_1/mailboxes', null, []]
    || $growth->calls[9] !== ['POST', '/partner/domains/dom_1/mailboxes', ['localPart' => 'hello', 'sendPasswordLink' => true], ['Idempotency-Key: mailbox-1']]
    || $growth->calls[10] !== ['PATCH', '/partner/domains/dom_1/mailboxes/mbx%2F1', ['displayName' => 'Femi A.'], []]
    || $growth->calls[11] !== ['POST', '/partner/domains/dom_1/mailboxes/mbx_1/password-link', ['reset' => true, 'send' => false], []]
    || $growth->calls[12][1] !== '/partner/domains/dom_1/mailboxes/mbx_1/pause'
    || $growth->calls[13][1] !== '/partner/domains/dom_1/mailboxes/mbx_1/resume'
    || $growth->calls[14] !== ['DELETE', '/partner/domains/dom_1/mailboxes/mbx_1', null, []]
    || $growth->calls[15] !== ['POST', '/partner/domains/dom_1/aliases', ['localPart' => 'orders', 'deliverTo' => 'hello@kicksbyfemi.ng'], []]
    || $growth->calls[16] !== ['PATCH', '/partner/domains/dom_1/aliases/als_1', ['deliverTo' => 'sales@kicksbyfemi.ng'], []]
    || $growth->calls[17] !== ['DELETE', '/partner/domains/dom_1/aliases/als_1', null, []]
    || $growth->calls[18] !== ['POST', '/partner/domains/dom_1/mail-migration', ['source' => 'google', 'accounts' => $accounts, 'switch' => 'tonight'], ['Idempotency-Key: migration-1']]
    || $growth->calls[19] !== ['GET', '/partner/domains/dom_1/mail-migration', null, []]
    || $growth->calls[20] !== ['POST', '/partner/domains/dom_1/mail-migration/switch', ['when' => 'now'], []]
    || $growth->calls[21][1] !== '/partner/domains/dom_1/mail-migration/cancel'
    || $growth->calls[22][1] !== '/partner/domains/dom_1/mail-migration/accounts/mbx%2F1/skip'
    || $growth->calls[23][1] !== '/partner/domains/dom_1/mail-migration/retry'
    || $growth->calls[24] !== ['POST', '/partner/domains/dom_1/mail-migration/accounts/mbx%2F1/retry', ['username' => 'femi@kicksbyfemi.ng', 'password' => 'new-app-password'], []]
    || $growth->calls[25] !== ['POST', '/partner/domains/dom_1/mail-migration/accounts/mbx_1/retry', null, []]
    || $growth->calls[26][1] !== '/partner/domains/dom%2F1/transfer-in/switch-nameservers'
) {
    throw new RuntimeException('Partner storefront, mailbox, transfer and migration contract failed');
}

echo "Partner SDK requests passed\n";
