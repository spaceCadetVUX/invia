<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

// Requires: composer require laravel/horizon predis/predis
// Setup:    php artisan horizon:install
class HorizonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (!class_exists(\Laravel\Horizon\Horizon::class)) {
            return;
        }

        \Laravel\Horizon\Horizon::auth(function (Request $request) {
            return $request->user()?->hasRole('admin') ?? false;
        });
    }
}
