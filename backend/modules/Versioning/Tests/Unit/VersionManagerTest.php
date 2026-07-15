<?php

declare(strict_types=1);

namespace Modules\Versioning\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Modules\Versioning\Application\Contracts\Versionable;
use Modules\Versioning\Application\Enums\VersionStatus;
use Modules\Versioning\Application\VersionManager;
use Modules\Versioning\Infrastructure\Concerns\HasVersions;
use RuntimeException;
use Tests\TestCase;

class VersionManagerTest extends TestCase
{
    use RefreshDatabase;

    private VersionManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        // A self-contained versionable model so the test exercises the generic
        // engine without depending on the Company module.
        Schema::create('fake_records', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('note')->nullable();
            $table->timestamps();
        });

        $this->manager = new VersionManager;
    }

    public function test_new_record_is_created_with_the_first_version(): void
    {
        $result = $this->manager->upsert(FakeRecord::class, ['title' => 'Alpha'], [
            'title' => 'Alpha',
            'note' => 'first',
        ]);

        $this->assertSame(VersionStatus::Created, $result->status);
        $this->assertSame(1, $result->version);
        $this->assertInstanceOf(FakeRecord::class, $result->model);
        $this->assertTrue($result->model->exists);
        $this->assertSame(1, $result->model->versions()->count());
    }

    public function test_identical_data_is_treated_as_a_duplicate(): void
    {
        $attributes = ['title' => 'Alpha', 'note' => 'first'];
        $this->manager->upsert(FakeRecord::class, ['title' => 'Alpha'], $attributes);

        $result = $this->manager->upsert(FakeRecord::class, ['title' => 'Alpha'], $attributes);

        $this->assertSame(VersionStatus::Duplicate, $result->status);
        $this->assertSame(1, $result->version);
        $this->assertSame(1, $result->model->versions()->count());
        $this->assertSame(1, FakeRecord::count());
    }

    public function test_changed_versioned_attribute_updates_and_records_a_new_version(): void
    {
        $this->manager->upsert(FakeRecord::class, ['title' => 'Alpha'], ['title' => 'Alpha']);

        $result = $this->manager->upsert(FakeRecord::class, ['title' => 'Alpha'], ['title' => 'Beta']);

        $this->assertSame(VersionStatus::Updated, $result->status);
        $this->assertSame(2, $result->version);
        $this->assertSame('Beta', $result->model->refresh()->title);
        $this->assertSame(2, $result->model->versions()->count());
        $this->assertSame(1, FakeRecord::count());
    }

    public function test_change_to_a_non_versioned_attribute_is_ignored_as_a_duplicate(): void
    {
        $this->manager->upsert(FakeRecord::class, ['title' => 'Alpha'], ['title' => 'Alpha', 'note' => 'first']);

        $result = $this->manager->upsert(FakeRecord::class, ['title' => 'Alpha'], ['title' => 'Alpha', 'note' => 'changed']);

        $this->assertSame(VersionStatus::Duplicate, $result->status);
        $this->assertSame(1, $result->model->versions()->count());
        // The non-versioned change is not persisted, because nothing was saved.
        $this->assertSame('first', $result->model->refresh()->note);
    }

    public function test_snapshot_captures_only_the_versioned_attributes(): void
    {
        $result = $this->manager->upsert(FakeRecord::class, ['title' => 'Alpha'], [
            'title' => 'Alpha',
            'note' => 'ignored-in-snapshot',
        ]);

        $snapshot = $result->model->versions()->firstOrFail();

        $this->assertSame(['title' => 'Alpha'], $snapshot->data);
    }

    public function test_a_failure_while_recording_the_version_rolls_back_the_whole_write(): void
    {
        // Seed an existing record at version 1 without going through the
        // throwing recordVersion() (versions()->create() writes the Version
        // model directly).
        $record = ExplodingRecord::query()->create(['title' => 'Alpha']);
        $record->versions()->create(['version' => 1, 'data' => ['title' => 'Alpha']]);

        try {
            $this->manager->upsert(ExplodingRecord::class, ['title' => 'Alpha'], ['title' => 'Beta']);
            $this->fail('Expected the version-recording failure to bubble up.');
        } catch (RuntimeException) {
            // Expected: recordVersion() blew up after the row was saved.
        }

        // The company update and the (never-created) version are both rolled
        // back — no partial persistence, proving the write is atomic.
        $this->assertSame('Alpha', $record->refresh()->title);
        $this->assertSame(1, $record->versions()->count());
        $this->assertSame(['title' => 'Alpha'], $record->versions()->firstOrFail()->data);
    }
}

class FakeRecord extends Model implements Versionable
{
    use HasVersions;

    protected $table = 'fake_records';

    protected $guarded = [];

    /**
     * @return array<int, string>
     */
    public function versionedAttributes(): array
    {
        return ['title'];
    }
}

/**
 * A versionable model whose version recording always fails, used to prove the
 * upsert rolls the whole write back when the version insert throws.
 */
class ExplodingRecord extends Model implements Versionable
{
    use HasVersions;

    protected $table = 'fake_records';

    protected $guarded = [];

    /**
     * @return array<int, string>
     */
    public function versionedAttributes(): array
    {
        return ['title'];
    }

    public function recordVersion(): int
    {
        throw new RuntimeException('Simulated version-recording failure.');
    }
}
