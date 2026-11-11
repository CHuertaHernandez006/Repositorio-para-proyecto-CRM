<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
        RateLimiter::for('webrtc-credentials', function (Request $request) {
            $usuario = $request->user();

            return Limit::perMinute(10)
                ->by('webrtc-user:' . (
                    $usuario
                        ? $usuario->getAuthIdentifier()
                        : $request->ip()
                ));
        });
    }
}
