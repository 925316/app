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
        Schema::create('heartbeat_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('license_id')->constrained('licenses')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');

            $table->string('hwid_hash', 64);

            $table->string('session_id', 255)->nullable();
            $table->string('client_version', 50);

            $table->string('ip_address', 45)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->text('user_agent')->nullable();

            $table->unsignedInteger('uptime_seconds')->nullable();
            $table->unsignedInteger('memory_usage_mb')->nullable();
            $table->boolean('is_offline_report')->default(false);

            $table->timestamp('next_heartbeat_expected')->nullable();
            $table->enum('session_status', ['active', 'idle', 'stale', 'dead'])->default('dead');

            $table->unsignedInteger('heartbeat_count')->default(0);
            $table->unsignedInteger('avg_heartbeat_interval')->nullable();
            $table->unsignedInteger('missed_heartbeats')->default(0);

            $table->timestamp('received_at')->useCurrent();

            $table->timestamps();


            $table->index(['license_id', 'received_at']);
            $table->index(['hwid_hash', 'received_at']);
            $table->index(['session_status', 'received_at']);
            $table->index(['session_id', 'session_status']);
            $table->index(['license_id', 'missed_heartbeats']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heartbeat_logs');
    }
};
