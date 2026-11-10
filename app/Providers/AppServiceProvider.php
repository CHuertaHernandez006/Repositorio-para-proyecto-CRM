
<?php


use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

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
        // Limitar solicitudes de credenciales SIP por operario.
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
