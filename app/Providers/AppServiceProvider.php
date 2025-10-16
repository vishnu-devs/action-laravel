<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $router = $this->app->make(\Illuminate\Routing\Router::class);
        $router->aliasMiddleware('verify.device', \App\Http\Middleware\VerifyDeviceFingerprint::class);
    }
}
