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
        Schema::create('client_sessions', function (Blueprint $table) {
            $table->id();

            $table->string('session_token', 255)->unique();

            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('license_id')->nullable()->constrained('licenses')->nullOnDelete();

            $table->string('hwid_hash', 64)->nullable();

            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();

            $table->string('client_version', 50);
            $table->string('language', 10)->default('en');

            $table->json('session_data')->nullable();

            $table->timestamp('last_heartbeat_at')->nullable();

            $table->timestamp('expires_at')->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->string('termination_reason', 255)->nullable();

            $table->timestamps();

            $table->index('last_heartbeat_at');
            $table->index(['account_id', 'expires_at']);
            $table->index(['expires_at', 'account_id']);
            $table->index(['license_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_sessions');
    }
};
