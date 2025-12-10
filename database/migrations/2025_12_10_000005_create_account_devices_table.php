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
        Schema::create('account_devices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');

            $table->string('hwid_hash', 64);
            $table->string('user_agent_hash', 64);
            $table->string('ip_address', 45);

            $table->char('country_code', 2)->nullable();

            $table->json('device_fingerprint')->nullable();

            $table->unsignedTinyInteger('reputation_score')->default(100);

            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->unsignedInteger('seen_count')->default(1);

            $table->boolean('is_active')->default(true);
            $table->boolean('is_trusted')->default(false);
            $table->boolean('is_suspicious')->default(false);

            $table->json('risk_factors')->nullable();

            $table->timestamps();


            $table->unique(['account_id', 'hwid_hash']);
            $table->index('hwid_hash');
            $table->index('is_active');
            $table->index('reputation_score');
            $table->index('last_seen_at');
            $table->index(['reputation_score', 'last_seen_at']);
            $table->index(['account_id', 'last_seen_at']);
            $table->index(['is_trusted', 'is_suspicious']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_devices');
    }
};
