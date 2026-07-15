<?php

declare(strict_types=1);

namespace Modules\Company\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Company\Infrastructure\Models\Company;

/**
 * Seeds example companies, each with an initial recorded version so the seeded
 * data respects the same "every company has at least version 1" invariant the
 * upsert endpoint guarantees.
 */
final class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $canonical = Company::query()->firstOrCreate(
            ['edrpou' => '37027819'],
            [
                'name' => 'ТОВ Українська енергетична біржа',
                'address' => '01001, Україна, м. Київ, вул. Хрещатик, 44',
            ],
        );

        if ($canonical->wasRecentlyCreated) {
            $canonical->recordVersion();
        }

        Company::factory()
            ->count(5)
            ->create()
            ->each(fn (Company $company) => $company->recordVersion());
    }
}
