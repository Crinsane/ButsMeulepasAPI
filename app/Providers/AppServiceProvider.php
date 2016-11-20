<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(Client::class, function () {
            $baseUrl = $this->app['config']->get('app.wp_url');

            return new Client([
                'base_uri' => $baseUrl . '/wp-json/wp/v2/',
                'headers' => ['Authorization' => 'Basic Um9iOkZvcm11bGUx'],
            ]);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
