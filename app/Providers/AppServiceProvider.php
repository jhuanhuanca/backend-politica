<?php

namespace App\Providers;

use App\Contracts\MilitantRegistry;
use App\Services\Militant\MilitantService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MilitantRegistry::class, MilitantService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/militancia'));

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            URL::forceRootUrl((string) config('app.url'));
        }

        RateLimiter::for('signatures', function (Request $request) {
            return Limit::perMinute(10)->by((string) ($request->user()?->id ?: $request->ip()));
        });
    }
}
