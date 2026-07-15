<?php

declare(strict_types=1);

namespace Modules\Company\Application\UseCases\UpsertCompany;

use Modules\Versioning\Application\Enums\VersionStatus;

/**
 * Result of upserting a company through the versioned write path.
 */
final readonly class UpsertCompanyResultDto
{
    public function __construct(
        public VersionStatus $status,
        public int $companyId,
        public int $version,
    ) {}
}
