<?php

declare(strict_types=1);

namespace Modules\Versioning\Application;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Modules\Versioning\Application\Contracts\Versionable;
use Modules\Versioning\Application\Enums\VersionStatus;

/**
 * Reusable versioned upsert for any {@see Versionable} model.
 *
 * This is the single write path that keeps the whole module universal: a new
 * route only needs its model to use the versioning trait and to call
 * {@see upsert()} — no per-model tables or duplicated create/update logic.
 */
final class VersionManager
{
    /**
     * How many extra times to retry when a concurrent request wins the race to
     * create the row (the retry re-reads the winner's row as update/duplicate).
     */
    private const MaxRetries = 1;

    /**
     * Create, update, or ignore a record and record a version when the
     * versioned data changes.
     *
     * Updates are serialized by locking the existing row. First-time creation
     * has no row to lock, so two concurrent creates race the unique lookup key;
     * the loser catches the unique-constraint violation and retries once,
     * finding the winner's row and resolving as updated/duplicate. Data
     * integrity is guaranteed by the unique constraint itself, not the retry.
     *
     * @param  class-string<Model&Versionable>  $modelClass
     * @param  array<string, mixed>  $lookup  Unique keys used to find an existing record.
     * @param  array<string, mixed>  $attributes  Full attribute set to persist.
     */
    public function upsert(string $modelClass, array $lookup, array $attributes): VersionResult
    {
        for ($attempt = 0; ; $attempt++) {
            try {
                return DB::transaction(fn (): VersionResult => $this->apply($modelClass, $lookup, $attributes));
            } catch (UniqueConstraintViolationException $exception) {
                if ($attempt >= self::MaxRetries) {
                    throw $exception;
                }
            }
        }
    }

    /**
     * @param  class-string<Model&Versionable>  $modelClass
     * @param  array<string, mixed>  $lookup
     * @param  array<string, mixed>  $attributes
     */
    private function apply(string $modelClass, array $lookup, array $attributes): VersionResult
    {
        /** @var (Model&Versionable)|null $model */
        $model = $modelClass::query()->where($lookup)->lockForUpdate()->first();

        if ($model === null) {
            /** @var Model&Versionable $model */
            $model = $modelClass::query()->create($attributes);

            return new VersionResult(VersionStatus::Created, $model, $model->recordVersion());
        }

        if ($model->fill($attributes)->isDirty($model->versionedAttributes())) {
            $model->save();

            return new VersionResult(VersionStatus::Updated, $model, $model->recordVersion());
        }

        return new VersionResult(VersionStatus::Duplicate, $model, $model->currentVersion());
    }
}
