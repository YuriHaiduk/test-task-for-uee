<?php

declare(strict_types=1);

namespace Modules\Versioning;

use Illuminate\Support\ServiceProvider;
use Modules\Versioning\Providers\VersioningDatabaseServiceProvider;

final class VersioningServiceProvider extends ServiceProvider
{
    /**
     * Register any module services.
     *
     * VersionManager is stateless and dependency-free, so it needs no explicit
     * binding — the container auto-resolves it.
     */
    public function register(): void
    {
        $this->app->register(VersioningDatabaseServiceProvider::class);
    }

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        //
    }
}
