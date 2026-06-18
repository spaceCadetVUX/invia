<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        RateLimiter::for('rsvp', function (Request $request) {
            return [
                Limit::perMinutes(10, 10)->by($request->ip()),
                Limit::perHour(200)->by('event:' . $request->route('slug')),
            ];
        });

        RateLimiter::for('wishes', function (Request $request) {
            return [
                Limit::perMinutes(10, 5)->by($request->ip()),
                Limit::perHour(500)->by('wishes-event:' . $request->route('slug')),
            ];
        });

        DB::listen(function ($query) {
            $threshold = config('app.debug') ? 500 : 2000;

            if ($query->time > $threshold) {
                Log::channel('slow_queries')->warning('Slow query', [
                    'sql'      => $query->sql,
                    'bindings' => $query->bindings,
                    'time_ms'  => $query->time,
                ]);
            }
        });
    }
}
