# StackShift Assets for PHP

Use \`StackShift\AssetsClient\` from Composer. Laravel applications may register it as a singleton and pass \`UploadedFile::getRealPath()\` to upload or replace. Mutations require the current revision; resumable-session creation accepts an idempotency key.

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
