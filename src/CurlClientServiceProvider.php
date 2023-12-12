<?php

namespace Ok\CurlClient;

use Illuminate\Support\ServiceProvider;

class CurlClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/curl_client.php', 'curl_client'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/curl_client.php' => config_path('curl_client.php'),
        ]);
    }
}