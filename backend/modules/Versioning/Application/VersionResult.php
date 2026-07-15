<?php

declare(strict_types=1);

namespace Modules\Versioning\Application;

use Illuminate\Database\Eloquent\Model;
use Modules\Versioning\Application\Contracts\Versionable;
use Modules\Versioning\Application\Enums\VersionStatus;

/**
 * Typed outcome of {@see VersionManager::upsert()}.
 */
final readonly class VersionResult
{
    public function __construct(
        public VersionStatus $status,
        public Model&Versionable $model,
        public int $version,
    ) {}
}
