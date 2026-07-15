<?php

declare(strict_types=1);

namespace Modules\Versioning\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * An immutable snapshot of a versionable model's data at a point in time.
 *
 * @property int $id
 * @property string $versionable_type
 * @property int $versionable_id
 * @property int $version
 * @property array<string, mixed> $data
 * @property Carbon|null $created_at
 */
class Version extends Model
{
    /**
     * Versions are append-only: only the creation timestamp is tracked.
     */
    public const UPDATED_AT = null;

    /**
     * Enforce the append-only invariant: once written, a version can never be
     * changed or removed through Eloquent. This turns accidental history
     * corruption ($version->update(...) / $version->delete()) into a hard error.
     *
     * Note: mass update/delete queries bypass model events, so this guards the
     * Eloquent instance path only — the intended write path never mutates a row.
     */
    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new LogicException('Versions are append-only and cannot be updated.');
        });

        static::deleting(function (): never {
            throw new LogicException('Versions are append-only and cannot be deleted.');
        });
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'version',
        'data',
    ];

    /**
     * The parent versionable model.
     *
     * @return MorphTo<Model, $this>
     */
    public function versionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'data' => 'array',
        ];
    }
}
