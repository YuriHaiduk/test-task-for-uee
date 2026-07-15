<?php

declare(strict_types=1);

namespace Modules\Company\Application\UseCases\ListCompanyVersions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Company\Infrastructure\Models\Company;
use Modules\Versioning\Application\VersionSnapshot;

/**
 * Returns the full version history of a company as framework-agnostic snapshots.
 */
final readonly class ListCompanyVersionsService
{
    /**
     * @return array<int, VersionSnapshot>
     *
     * @throws ModelNotFoundException Mapped to a 404 centrally in bootstrap/app.php.
     */
    public function handle(string $edrpou): array
    {
        return Company::query()
            ->where('edrpou', $edrpou)
            ->firstOrFail()
            ->versionSnapshots();
    }
}
