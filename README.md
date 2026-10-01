# StackShift Assets for PHP

Use \`StackShift\AssetsClient\` from Composer. Laravel applications may register it as a singleton and pass \`UploadedFile::getRealPath()\` to upload or replace. Mutations require the current revision; resumable-session creation accepts an idempotency key.

## AI credits

AI credits are account-scoped. Inspect usage, start a top-up, or select managed billing versus your own provider key from the server-side client:

```php
$balance = $assets->aiCreditBalance();
$usage = $assets->aiCreditUsage(['product' => 'assets', 'limit' => 25]);
$quote = $assets->quoteAICredits(1000, 'USD');
$checkout = $assets->checkoutAICredits($quote['id'], $idempotencyKey, 'paystack');
$funding = $assets->setAIFundingMode('assets', 'managed');
```

Keep the account API key and checkout creation on your server.

## Native video, governed DAM and product rendering

Use the space-scoped DAM and video clients for collaborators, metadata schemas, bucket governance, reviewed publications, picker capabilities, model preparation, product rendering, galleries, version-pinned encoding, captions and playback sessions.

```php
$dam = $assets->dam($spaceId);
$video = $assets->video($spaceId);
$schema = $dam->createMetadataSchema(['name' => 'Products', 'fields' => [[
    'key' => 'product_name', 'label' => 'Product name', 'type' => 'text', 'required' => true,
]]]);
$published = $dam->publishMetadataSchema($schema['id'], $schema['revision']);
$workspace = $video->workspace($assetId);
```

Mutations carry the supplied revision in `If-Match`. Model preparation and rendering carry an idempotency key; render submission also carries the accepted maximum units. Playback renewal and events authenticate with the session credential, not the account key. Keep these clients on your backend and return grants with `Cache-Control: private, no-store`.

See the [Assets SDK guide](https://docs.stackshift.cloud/assets/sdk-media-workflows) and its linked feature guides for complete language examples, inputs, permissions, review transitions and browser callbacks.

## Assets storage and Cloudinary migration

Use `$assets->forAssetSpace($spaceId)` for connection, S2 bucket, storage configuration, and migration calls. Run a dry migration and inspect its report before transferring originals.

```php
$platform = $assets->forAssetSpace($spaceId);
$preview = $platform->startAssetMigration([
    'source_connection_id' => $connectionId, 'destination_bucket_id' => $bucketId,
    'source_prefix' => 'campaigns/2026', 'dry_run' => true,
]);
$report = $platform->assetMigrationReport($preview['id']);
```
