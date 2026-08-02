<?php

declare(strict_types=1);

namespace StackShift\Laravel;

use Illuminate\Support\ServiceProvider;
use StackShift\AssetsClient;

final class StackShiftAssetsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AssetsClient::class, static function (): AssetsClient {
            $apiKey = (string) config('services.stackshift.api_key', '');
            $baseUrl = (string) config('services.stackshift.base_url', 'https://api.stackshift.cloud/api/v1');
            $cdnBaseUrl = (string) config('services.stackshift.cdn_base_url', 'https://cdn.stackshift.cloud');
            return new AssetsClient($apiKey, $baseUrl, $cdnBaseUrl);
        });
    }
}
