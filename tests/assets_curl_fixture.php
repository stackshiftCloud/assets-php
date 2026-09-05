<?php

namespace StackShift;

function curl_init(string $url): object { return (object) ['url' => $url, 'options' => []]; }
function curl_setopt_array(object $handle, array $options): bool { $handle->options = $options; return true; }
function curl_exec(object $handle): string {
    $GLOBALS['requests'][] = $handle;
    return json_encode(['success' => true, 'data' => ['id' => 'result']], JSON_THROW_ON_ERROR);
}
function curl_getinfo(object $handle, int $option): int {
    return $option === CURLOPT_HEADER ? 0 : ($option === CURLINFO_RESPONSE_CODE ? ($GLOBALS['responseStatus'] ?? 200) : 0);
}
function curl_close(object $handle): void {}
