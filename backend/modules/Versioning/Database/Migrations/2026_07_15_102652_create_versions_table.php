<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A single polymorphic table stores version snapshots for any versionable
     * model. Rows are append-only, so only a creation timestamp is tracked.
     */
    public function up(): void
    {
        Schema::create('versions', function (Blueprint $table) {
            $table->id();
            // Explicit polymorphic columns (no auto index): the unique index
            // below already covers lookups on the (type, id) prefix.
            $table->string('versionable_type');
            $table->unsignedBigInteger('versionable_id');
            $table->unsignedInteger('version');
            $table->jsonb('data');
            $table->timestamp('created_at')->nullable();

            $table->unique(['versionable_type', 'versionable_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versions');
    }
};
