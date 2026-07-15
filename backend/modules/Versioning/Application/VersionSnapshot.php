<?php

declare(strict_types=1);

namespace Modules\Versioning\Application;

use Carbon\CarbonInterface;

/**
 * An immutable, read-only view of a recorded version, decoupled from the
 * Infrastructure Eloquent model and exposed to the Presentation layer so it
 * never touches persistence internals.
 */
final readonly class VersionSnapshot
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public int $version,
        public array $data,
        public ?CarbonInterface $createdAt,
    ) {}
}
