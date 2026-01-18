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
            
            $table->string('session_token', 128)->unique()->nullable(false);
            $table->foreignId('account_id')
                ->constrained('accounts')
                ->onDelete('cascade');
            $table->foreignId('device_id')
                ->constrained('account_devices')
                ->onDelete('cascade');
            $table->binary('ip_address', 16)->nullable(false);
            $table->string('client_version', 50)->nullable(false);
            $table->timestamp('last_heartbeat_at')->nullable();
            
            $table->timestamps();

            $table->index('last_heartbeat_at');
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
