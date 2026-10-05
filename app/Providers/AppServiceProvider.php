<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // General API budget for authenticated and anonymous callers.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Much tighter budget for agent lifecycle endpoints: registration mints a
        // credential and fetch-script returns executable payload code, so both
        // are prime targets for credential stuffing / brute-force licence guessing.
        RateLimiter::for('agent-auth', function (Request $request) {
            $key = $request->header('X-Agent-Key')
                ? 'agent:'.hash('sha256', (string) $request->header('X-Agent-Key'))
                : 'ip:'.$request->ip();

            return [
                Limit::perMinute(30)->by($key),
                Limit::perHour(500)->by($key),
            ];
        });

        // Script fetches are frequent but must not be brute-forced against
        // licence keys: allow ~10/min per authenticated user.
        RateLimiter::for('agent-script', function (Request $request) {
            return Limit::perMinute(10)->by((string) ($request->user()?->id ?: $request->ip()));
        });
    }
}
