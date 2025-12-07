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
        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('license_id')->nullable()->constrained('licenses')->onDelete('set null');
            $table->enum('event_type', ['security', 'activation', 'system', 'user_action']);
            $table->string('action_code', 50);
            $table->char('reason_code', 3)->nullable();
            $table->string('detail_id', 10)->nullable();
            $table->string('trigger_ip', 45)->nullable();
            $table->text('trigger_user_agent')->nullable();
            $table->json('details');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->index('user_id');
            $table->index('license_id');
            $table->index('performed_at');
            $table->index('event_type');
            $table->index('action_code');
            $table->index('reason_code');
            $table->index(['event_type', 'performed_at']);
            $table->index(['user_id', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_logs');
    }
};
