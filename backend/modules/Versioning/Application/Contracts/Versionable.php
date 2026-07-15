<?php

declare(strict_types=1);

namespace Modules\Versioning\Application\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A model whose changes are tracked by the versioning module.
 *
 * Implement this contract and use the HasVersions trait to make any Eloquent
 * model versionable. The contract stays in the Application layer and references
 * only framework abstractions, so it never depends on the concrete
 * Infrastructure persistence model (no `@see` FQCN here, to keep it that way).
 * This is a framework-aware Laravel application layer (it builds on Eloquent
 * and morph relations); it is reusable across models, not framework-independent.
 *
 * Attribute contract: every persisted attribute of an implementing model must
 * also be a versioned attribute. Only versioned attributes are snapshotted and
 * compared for changes, so a persisted-but-unversioned attribute would be
 * ignored by change detection and silently dropped on save. Keep the persisted
 * set and versionedAttributes() aligned.
 */
interface Versionable
{
    /**
     * Attribute names captured in every version snapshot and compared to
     * decide whether the data has actually changed.
     *
     * @return array<int, string>
     */
    public function versionedAttributes(): array;

    /**
     * All recorded versions for this model, newest first.
     *
     * @return MorphMany<Model, static>
     */
    public function versions(): MorphMany;

    /**
     * Persist the current versioned attributes as the next version and return
     * that version's number.
     */
    public function recordVersion(): int;

    /**
     * The latest version number, or 0 when no version exists yet.
     */
    public function currentVersion(): int;
}
