<?php

namespace Ok\CurlClient;

use Illuminate\Support\ServiceProvider;

class CurlClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/curl_client.php',
            'curl_client'
        );

        $this->app->bind(CurlClient::class, function ($app) {
            return new CurlClient($app['config']->get('curl_client.script_path'));
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/curl_client.php' => config_path('curl_client.php'),
            ], 'curl-client-config');
        }
    }
}
