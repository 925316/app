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
        Schema::create('license_upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('old_license_id')->constrained('licenses')->onDelete('cascade');
            $table->foreignId('new_license_id')->constrained('licenses')->onDelete('cascade');
            $table->timestamp('upgraded_at')->useCurrent();
            
            $table->unique(['old_license_id']);
            $table->unique(['new_license_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_upgrades');
    }
};
