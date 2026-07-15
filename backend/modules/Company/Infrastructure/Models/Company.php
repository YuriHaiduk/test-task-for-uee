<?php

declare(strict_types=1);

namespace Modules\Company\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Company\Database\Factories\CompanyFactory;
use Modules\Versioning\Application\Contracts\Versionable;
use Modules\Versioning\Infrastructure\Concerns\HasVersions;

/**
 * @property int $id
 * @property string $name
 * @property string $edrpou
 * @property string $address
 */
class Company extends Model implements Versionable
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    use HasVersions;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'edrpou',
        'address',
    ];

    /**
     * Attributes captured in each version and compared for changes.
     *
     * @return array<int, string>
     */
    public function versionedAttributes(): array
    {
        return ['name', 'edrpou', 'address'];
    }

    /**
     * The model lives outside the default App\Models namespace, so point
     * Eloquent at the module factory explicitly.
     *
     * @return Factory<static>
     */
    protected static function newFactory(): Factory
    {
        return CompanyFactory::new();
    }
}
