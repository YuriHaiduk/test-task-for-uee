<?php

declare(strict_types=1);

namespace Modules\Company\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class CompanyRouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap module routes.
     *
     * The task mandates the literal `/api/company` path, so the module keeps
     * the plain `api` prefix rather than the reference project's `api/v1`.
     */
    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group($this->modulePath('Presentation/Http/routes/api.php'));
    }

    private function modulePath(string $path): string
    {
        return dirname(__DIR__).'/'.$path;
    }
}
