<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Omines\OAuth2\Client\Provider\Gitlab;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Gitlab::class, function () {
            return new Gitlab([
                'clientId' => config('app.gitlab.clientId'),
                'clientSecret' => config('app.gitlab.clientSecret'),
                'redirectUrl' => 'http://localhost:8010/comforter',
                'domain' => 'http://localhost:4000'
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Fix for older sql
        Schema::defaultStringLength(191);
    }
}
