<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\Section;
use App\Models\Setting;
use App\Models\User;
use App\Observers\AssetObserver;
use App\Observers\SectionObserver;
use App\Observers\SettingObserver;
use App\Observers\UserObserver;
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
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email.$request->ip());
        });

        // Audit log observers — write a row on every create/update/delete
        Section::observe(SectionObserver::class);
        Asset::observe(AssetObserver::class);
        User::observe(UserObserver::class);
        Setting::observe(SettingObserver::class);
    }
}
