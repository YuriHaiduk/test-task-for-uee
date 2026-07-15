<?php

declare(strict_types=1);

namespace Modules\Company\Application\UseCases\UpsertCompany;

use Modules\Company\Infrastructure\Models\Company;
use Modules\Versioning\Application\VersionManager;

/**
 * Creates or updates a company and records a version on change, delegating the
 * generic versioned-upsert mechanics to the reusable versioning module.
 */
final readonly class UpsertCompanyService
{
    public function __construct(private VersionManager $versions) {}

    public function handle(UpsertCompanyData $data): UpsertCompanyResultDto
    {
        $result = $this->versions->upsert(
            Company::class,
            ['edrpou' => $data->edrpou],
            $data->toAttributes(),
        );

        return new UpsertCompanyResultDto(
            status: $result->status,
            companyId: $result->model->id,
            version: $result->version,
        );
    }
}
