<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\AssetRepositoryInterface::class,
            \App\Repositories\Eloquent\AssetRepository::class
        );
        $this->app->bind(
            \App\Repositories\SectionRepositoryInterface::class,
            \App\Repositories\Eloquent\SectionRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
