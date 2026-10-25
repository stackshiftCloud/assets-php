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
require __DIR__ . '/assets_curl_fixture.php';
$assets = new AssetsClient('account', 'https://example.test/api/v1');

$definition = ['operations' => [['type' => 'resize', 'width' => 640]]];
$estimate = $assets->estimateTransform('asset/1', 'version/1', $definition);
$request = end($requests);
check($estimate['id'] === 'result', 'Estimate response not unwrapped');
check($request->options[CURLOPT_CUSTOMREQUEST] === 'POST', 'Estimate method mismatch');
check(str_ends_with($request->url, '/assets/asset%2F1/transforms/estimate'), 'Estimate path mismatch');
check(json_decode($request->options[CURLOPT_POSTFIELDS], true) === ['version_id' => 'version/1', 'definition' => $definition], 'Estimate body mismatch');

$assets->usage('asset/1');
$request = end($requests);
check($request->options[CURLOPT_CUSTOMREQUEST] === 'GET', 'Usage method mismatch');
check(str_ends_with($request->url, '/assets/asset%2F1/usage'), 'Usage path mismatch');
check($request->options[CURLOPT_POSTFIELDS] === null, 'Usage must not send a body');

$assets->hold('asset/1');
$request = end($requests);
check($request->options[CURLOPT_CUSTOMREQUEST] === 'GET', 'Hold method mismatch');
check(str_ends_with($request->url, '/assets/asset%2F1/hold'), 'Hold path mismatch');
check($request->options[CURLOPT_POSTFIELDS] === null, 'Hold must not send a body');

$assets->createReuploadRequest('asset/1');
$request = end($requests);
check($request->options[CURLOPT_CUSTOMREQUEST] === 'POST', 'Re-upload request method mismatch');
check(str_ends_with($request->url, '/assets/asset%2F1/reupload-requests'), 'Re-upload request path mismatch');
check($request->options[CURLOPT_POSTFIELDS] === null, 'Empty re-upload request must not send a JSON array');

$assets->createReuploadRequest('asset/1', ['email' => 'owner@example.test', 'message' => 'Please send the original']);
check(json_decode(end($requests)->options[CURLOPT_POSTFIELDS], true) === ['email' => 'owner@example.test', 'message' => 'Please send the original'], 'Re-upload request body mismatch');

$assets->reuploadRequests('asset/1');
$request = end($requests);
check($request->options[CURLOPT_CUSTOMREQUEST] === 'GET', 'Re-upload list method mismatch');
check(str_ends_with($request->url, '/assets/asset%2F1/reupload-requests'), 'Re-upload list path mismatch');
check($request->options[CURLOPT_POSTFIELDS] === null, 'Re-upload list must not send a body');

echo "Assets asset operations PHP request tests passed\n";
