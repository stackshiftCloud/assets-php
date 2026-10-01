<?php

declare(strict_types=1);

use StackShift\AssetsClient;

spl_autoload_register(function (string $class): void {
    $path = __DIR__ . '/../src/' . str_replace('StackShift\\', '', $class) . '.php';
    if (is_file($path)) { require_once $path; }
});

$requests = [];
require __DIR__ . '/assets_curl_fixture.php';
$assets = new AssetsClient('account', 'https://example.test/api/v1');
$assets->createAssetConnection(['provider' => 'cloudinary']);
if (!str_ends_with(end($requests)->url, '/assets/connections') ||
    json_decode(end($requests)->options[CURLOPT_POSTFIELDS], true)['provider'] !== 'cloudinary') {
    throw new RuntimeException('Cloudinary connection request mismatch');
}
$assets = $assets->forAssetSpace('space-1');
$assets->configureAssetStorage('bucket/one', 7, ['storage_mode' => 'stackshift_s2']);
$request = end($requests);
if (!str_ends_with($request->url, '/assets/buckets/bucket%2Fone/storage') ||
    !in_array('If-Match: "7"', $request->options[CURLOPT_HTTPHEADER], true) ||
    !in_array('X-Asset-Space-ID: space-1', $request->options[CURLOPT_HTTPHEADER], true)) {
    throw new RuntimeException('Storage revision request mismatch');
}
$assets->startAssetMigration(['destination_bucket_id' => 'bucket', 'source_prefix' => 'campaign/']);
if (json_decode(end($requests)->options[CURLOPT_POSTFIELDS], true)['source_prefix'] !== 'campaign/') {
    throw new RuntimeException('Migration scope request mismatch');
}
$assets->assetMigrationReport('migration/one');
if (!str_ends_with(end($requests)->url, '/assets/migrations/migration%2Fone/report?format=json')) {
    throw new RuntimeException('Migration report request mismatch');
}
echo "Assets platform PHP request tests passed\n";
