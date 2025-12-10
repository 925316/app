<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('package_releases', function (Blueprint $table) {
            $table->id();

            $table->string('version', 50)->unique();
            $table->enum('release_channel', ['stable', 'beta', 'alpha', 'dev'])->default('stable');

            $table->unsignedTinyInteger('min_license_tier')->default(1);

            $table->string('download_url', 255);
            $table->char('checksum_sha256', 64)->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();

            $table->text('changelog')->nullable();

            $table->boolean('is_critical')->default(false);
            $table->boolean('is_force_update')->default(false);

            $table->timestamp('release_date');
            $table->timestamp('end_of_support')->nullable();

            $table->unsignedInteger('download_count')->default(0);

            $table->timestamps();


            $table->index('is_critical');
            $table->index(['release_channel', 'release_date']);
            $table->index(['min_license_tier', 'release_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_releases');
    }
};
