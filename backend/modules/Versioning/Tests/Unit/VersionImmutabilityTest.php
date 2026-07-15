<?php

declare(strict_types=1);

namespace Modules\Versioning\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Modules\Versioning\Infrastructure\Models\Version;
use Tests\TestCase;

class VersionImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_recorded_version_cannot_be_updated(): void
    {
        $version = $this->recordVersion();

        $this->expectException(LogicException::class);

        $version->update(['data' => ['title' => 'Tampered']]);
    }

    public function test_a_recorded_version_cannot_be_deleted(): void
    {
        $version = $this->recordVersion();

        try {
            $version->delete();
            $this->fail('Expected deleting a version to be rejected.');
        } catch (LogicException) {
            // Expected: history is append-only.
        }

        $this->assertSame(1, Version::query()->count());
    }

    private function recordVersion(): Version
    {
        $version = new Version;
        $version->versionable_type = 'fake';
        $version->versionable_id = 1;
        $version->version = 1;
        $version->data = ['title' => 'Alpha'];
        $version->save();

        return $version;
    }
}
