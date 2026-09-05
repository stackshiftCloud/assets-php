<?php

declare(strict_types=1);

use StackShift\AssetsClient;

spl_autoload_register(function (string $class): void {
    $path = __DIR__ . '/../src/' . str_replace('StackShift\\', '', $class) . '.php';
    if (is_file($path)) { require_once $path; }
});

function check(bool $condition, string $message): void
{
    if (!$condition) { throw new RuntimeException($message); }
}

$requests = [];
// Exercise the real AssetsClient closures and JSON encoding without external requests.
require __DIR__ . '/assets_curl_fixture.php';
$assets = new AssetsClient('account', 'https://example.test/custom/api/v1');
$dam = $assets->dam('space-1');
check($dam->metadataEditor('asset/1')['id'] === 'result', 'Response not unwrapped');
check(str_ends_with(end($requests)->url, '/assets/asset%2F1/metadata-editor'), 'ID not escaped');
$dam->transitionPublication('pub', 3, 'request-changes', 'Correct attribution');
check(in_array('If-Match: "3"', end($requests)->options[CURLOPT_HTTPHEADER], true), 'Missing revision');
check(json_decode(end($requests)->options[CURLOPT_POSTFIELDS], true)['note'] === 'Correct attribution', 'Missing review note');
$input = ['operation' => 'estimate', 'outputs' => ['proof']];
$dam->renderProduct('model', 4, 'request-1', $input, 0);
$request = end($requests);
check($input['operation'] === 'estimate', 'Caller input mutated');
check(json_decode($request->options[CURLOPT_POSTFIELDS], true)['maximum_units'] === 0, 'Zero budget lost');
check(in_array('X-Asset-Space-ID: space-1', $request->options[CURLOPT_HTTPHEADER], true), 'Missing space');
check(in_array('Idempotency-Key: request-1', $request->options[CURLOPT_HTTPHEADER], true), 'Missing admission key');
$dam->saveBucketGovernance('bucket', ['enabled' => false, 'channels' => [], 'revision' => 3]);
check(json_decode(end($requests)->options[CURLOPT_POSTFIELDS], true)['enabled'] === false, 'False lost');
$dam->transitionGallery('gallery', 8, 'publish');
check(str_ends_with(end($requests)->url, '/galleries/gallery/publish'), 'Wrong gallery route');
$dam->createPickerCapability(['channel' => 'web', 'origins' => ['https://cms.example.com']]);
check(str_ends_with(end($requests)->url, '/assets/picker-capabilities'), 'Wrong picker route');
$video = $assets->video('space-1');
$video->addCaption('a/b', 'v/1', ['language' => 'en', 'is_default' => false]);
check(str_ends_with(end($requests)->url, '/assets/a%2Fb/versions/v%2F1/video/captions'), 'Caption not version pinned');
$video->renewSession('session-key');
$request = end($requests);
check($request->url === 'https://example.test/playback/sessions/renew', 'Wrong playback origin');
check($request->options[CURLOPT_HTTPHEADER] === ['Content-Type: application/json', 'Authorization: Bearer session-key'], 'Playback credential contamination');
check($request->options[CURLOPT_POSTFIELDS] === '{}', 'Renewal body must be an object');
$video->events('session-key', [['sequence' => 0, 'kind' => 'startup', 'value' => 0]]);
check(json_decode(end($requests)->options[CURLOPT_POSTFIELDS], true)['events'][0]['sequence'] === 0, 'Event sequence lost');
$count = count($requests);
try { $video->renewSession(' '); throw new LogicException('Empty credential accepted'); }
catch (RuntimeException $error) { check(count($requests) === $count, 'Empty credential sent'); }
$responseStatus = 403;
try { $video->renewSession('session-key'); throw new LogicException('Access denial ignored'); }
catch (RuntimeException $error) { check(count($requests) === $count + 1, 'Retried denial'); }
echo "Assets DAM/video PHP request tests passed\n";
