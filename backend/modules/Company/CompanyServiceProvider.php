<?php

declare(strict_types=1);

namespace Modules\Company;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Modules\Company\Infrastructure\Models\Company;
use Modules\Company\Providers\CompanyDatabaseServiceProvider;
use Modules\Company\Providers\CompanyRouteServiceProvider;

final class CompanyServiceProvider extends ServiceProvider
{
    /**
     * Register any module services.
     */
    public function register(): void
    {
        $this->app->register(CompanyRouteServiceProvider::class);
        $this->app->register(CompanyDatabaseServiceProvider::class);
    }

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        // Store a stable morph alias instead of the model's FQCN, so version
        // history survives the class being moved or renamed. Merge-only (not
        // enforceMorphMap) to avoid requiring aliases for unrelated morphs.
        Relation::morphMap([
            'company' => Company::class,
        ]);
    }
}
