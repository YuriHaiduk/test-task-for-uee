<?php

declare(strict_types=1);

namespace Modules\Versioning\Providers;

use Illuminate\Support\ServiceProvider;

final class VersioningDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap module database resources.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom($this->modulePath('Database/Migrations'));
    }

    private function modulePath(string $path): string
    {
        return dirname(__DIR__).'/'.$path;
    }
}
