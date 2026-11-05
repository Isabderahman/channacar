<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            // This limiter runs before `auth:sanctum`, so `$request->user()` is
            // always null here — every admin request used to fall back to the IP,
            // and behind the Nuxt proxy all traffic shares one internal IP. A
            // gallery upload is one request per photo, so a big batch hit 429
            // ("Too Many Attempts.") part-way. Resolve the bearer token
            // explicitly and give each signed-in admin their own, larger budget.
            $user = $request->user('sanctum');

            if ($user) {
                return Limit::perMinute(600)->by('admin:'.$user->getAuthIdentifier());
            }

            return Limit::perMinute(60)->by($request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
