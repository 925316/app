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
        Schema::create('license_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->onDelete('cascade');
            $table->string('hwid_hash', 255);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent_hash', 255)->nullable();
            $table->boolean('is_current')->default(true);
            $table->boolean('trusted')->default(true);
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamp('unbound_at')->nullable();
            $table->timestamps();
            
            $table->index('license_id');
            $table->index('hwid_hash');
            $table->index('last_seen_at');
            $table->index('is_current');
            $table->index(['license_id', 'is_current']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_devices');
    }
};
