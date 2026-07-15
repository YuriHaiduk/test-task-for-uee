<?php

declare(strict_types=1);

namespace Modules\Versioning\Application\Enums;

/**
 * Outcome of a versioned upsert.
 */
enum VersionStatus: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Duplicate = 'duplicate';
}
