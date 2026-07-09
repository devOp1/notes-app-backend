<?php

namespace App\Providers;


use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

use Laravel\Passport\Passport;

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
    public function boot()
    {
        Validator::extend('captcha', function ($attribute, $value) { // Fake captcha must be empty to confuse bots
            return empty($value);
        }, 'Captcha is required.');

        Passport::enablePasswordGrant();
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));
        User::observe(UserObserver::class);
    }

}
