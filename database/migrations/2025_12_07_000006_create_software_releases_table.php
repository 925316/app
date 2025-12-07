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
        Schema::create('software_releases', function (Blueprint $table) {
            $table->id();
            $table->string('version', 50)->unique();
            $table->string('download_url', 255);
            $table->text('changelog')->nullable();
            $table->boolean('force_update')->default(false);
            $table->timestamp('release_date');
            $table->timestamps();
            
            $table->index(['release_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_releases');
    }
};
