<?php

declare(strict_types=1);

namespace Modules\Company\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Company\Infrastructure\Models\Company;
use Tests\TestCase;

class CompanyVersioningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<string, string>
     */
    private array $payload = [
        'name' => 'ТОВ Українська енергетична біржа',
        'edrpou' => '37027819',
        'address' => '01001, Україна, м. Київ, вул. Хрещатик, 44',
    ];

    public function test_new_company_is_created_with_first_version(): void
    {
        $response = $this->postJson('/api/company', $this->payload);

        $response->assertCreated()->assertExactJson([
            'status' => 'created',
            'company_id' => 1,
            'version' => 1,
        ]);

        $company = Company::firstOrFail();
        $this->assertSame(1, $company->versions()->count());
        $this->assertSame($this->payload['address'], $company->versions()->first()->data['address']);
    }

    public function test_identical_payload_is_treated_as_duplicate(): void
    {
        $this->postJson('/api/company', $this->payload)->assertCreated();

        $response = $this->postJson('/api/company', $this->payload);

        $response->assertOk()->assertJson([
            'status' => 'duplicate',
            'version' => 1,
        ]);

        $this->assertSame(1, Company::firstOrFail()->versions()->count());
    }

    public function test_changed_field_updates_company_and_records_new_version(): void
    {
        $this->postJson('/api/company', $this->payload)->assertCreated();

        $response = $this->postJson('/api/company', [
            ...$this->payload,
            'address' => 'НОВА АДРЕСА, Київ',
        ]);

        $response->assertOk()->assertJson([
            'status' => 'updated',
            'version' => 2,
        ]);

        $company = Company::firstOrFail();
        $this->assertSame('НОВА АДРЕСА, Київ', $company->address);
        $this->assertSame(2, $company->versions()->count());
    }

    public function test_earlier_version_snapshots_stay_immutable_after_updates(): void
    {
        $this->postJson('/api/company', $this->payload)->assertCreated();
        $this->postJson('/api/company', [
            ...$this->payload,
            'name' => 'Оновлена назва',
            'address' => 'Нова адреса',
        ])->assertOk();

        $company = Company::firstOrFail();
        $firstVersion = $company->versions()->where('version', 1)->firstOrFail();
        $secondVersion = $company->versions()->where('version', 2)->firstOrFail();

        // Version 1 must still hold the original values, untouched by the update.
        $this->assertSame($this->payload['name'], $firstVersion->data['name']);
        $this->assertSame($this->payload['address'], $firstVersion->data['address']);

        // Version 2 captures the new values.
        $this->assertSame('Оновлена назва', $secondVersion->data['name']);
        $this->assertSame('Нова адреса', $secondVersion->data['address']);
    }

    public function test_duplicate_request_does_not_modify_the_company(): void
    {
        $this->postJson('/api/company', $this->payload)->assertCreated();

        $company = Company::firstOrFail();
        $originalUpdatedAt = $company->updated_at;

        // Move time forward so any stray save() would change updated_at.
        $this->travel(1)->minutes();

        $this->postJson('/api/company', $this->payload)
            ->assertOk()
            ->assertJson(['status' => 'duplicate', 'version' => 1]);

        $company->refresh();
        $this->assertTrue($originalUpdatedAt->equalTo($company->updated_at));
        $this->assertSame(1, $company->versions()->count());
    }

    public function test_leading_whitespace_is_normalized_and_treated_as_duplicate(): void
    {
        $this->postJson('/api/company', $this->payload)->assertCreated();

        $this->postJson('/api/company', [
            ...$this->payload,
            'address' => '   '.$this->payload['address'].'   ',
        ])
            ->assertOk()
            ->assertJson(['status' => 'duplicate', 'version' => 1]);

        $this->assertSame(1, Company::firstOrFail()->versions()->count());
    }

    public function test_versions_endpoint_returns_history_newest_first(): void
    {
        $this->postJson('/api/company', $this->payload)->assertCreated();
        $this->postJson('/api/company', [...$this->payload, 'name' => 'Оновлена назва'])->assertOk();

        $response = $this->getJson('/api/company/37027819/versions');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.version', 2)
            ->assertJsonPath('data.1.version', 1)
            ->assertJsonPath('data.0.snapshot.name', 'Оновлена назва');
    }

    public function test_versions_endpoint_returns_404_json_for_unknown_company(): void
    {
        $this->getJson('/api/company/00000000/versions')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Resource not found.']);
    }

    public function test_api_errors_render_as_json_without_an_accept_header(): void
    {
        // Plain get() sends no Accept: application/json header.
        $this->get('/api/company/00000000/versions')
            ->assertNotFound()
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson(['message' => 'Resource not found.']);
    }

    public function test_request_is_validated(): void
    {
        $this->postJson('/api/company', ['name' => '', 'edrpou' => '', 'address' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'edrpou', 'address']);
    }

    public function test_edrpou_longer_than_ten_characters_is_rejected(): void
    {
        $this->postJson('/api/company', [...$this->payload, 'edrpou' => '123456789012'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['edrpou']);
    }
}
