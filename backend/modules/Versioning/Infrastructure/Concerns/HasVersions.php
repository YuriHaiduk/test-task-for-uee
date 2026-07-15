<?php

declare(strict_types=1);

namespace Modules\Versioning\Infrastructure\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Modules\Versioning\Application\Contracts\Versionable;
use Modules\Versioning\Application\VersionManager;
use Modules\Versioning\Application\VersionSnapshot;
use Modules\Versioning\Infrastructure\Models\Version;

/**
 * Gives an Eloquent model a polymorphic version history.
 *
 * This lives in Infrastructure because it is Eloquent persistence mechanics
 * (morphMany to the concrete {@see Version} model). Consuming models satisfy
 * {@see Versionable} by using it.
 *
 * Version recording is explicit (via {@see recordVersion()}) rather than tied
 * to model events, keeping the write path visible at the call site.
 *
 * Contract: version recording is guaranteed only through the VersionManager
 * write path. Direct Eloquent saves ($model->save()) and Query Builder bulk
 * updates (Model::query()->update(...)) bypass versioning by design and are
 * intentionally outside the supported contract.
 */
trait HasVersions
{
    /**
     * All recorded versions for this model, newest first.
     *
     * @return MorphMany<Version, $this>
     */
    public function versions(): MorphMany
    {
        return $this->morphMany(Version::class, 'versionable')->latest('version');
    }

    /**
     * Persist the current versioned attributes as the next version and return
     * that version's number.
     *
     * Callers serialize concurrent writers by locking the parent row (see
     * {@see VersionManager::upsert()}); the unique
     * constraint on (versionable, version) is the database-level safety net.
     */
    public function recordVersion(): int
    {
        $next = (int) $this->versions()->max('version') + 1;

        $this->versions()->create([
            'version' => $next,
            'data' => Arr::only($this->attributesToArray(), $this->versionedAttributes()),
        ]);

        return $next;
    }

    /**
     * The latest version number, or 0 when no version exists yet.
     */
    public function currentVersion(): int
    {
        return (int) $this->versions()->max('version');
    }

    /**
     * All recorded versions as framework-agnostic snapshot DTOs, newest first.
     *
     * @return array<int, VersionSnapshot>
     */
    public function versionSnapshots(): array
    {
        return $this->versions
            ->map(fn (Version $version): VersionSnapshot => new VersionSnapshot(
                version: $version->version,
                data: $version->data,
                createdAt: $version->created_at,
            ))
            ->all();
    }
}
