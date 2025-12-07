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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('license_key', 64)->unique();
            $table->enum('type', ['basic', 'vip']);
            $table->enum('status', ['unused', 'active', 'expired', 'suspended', 'upgraded'])->default('unused');
            $table->timestamp('hwid_reset_at')->nullable();
            $table->unsignedInteger('hwid_reset_count')->default(0);
            $table->timestamp('expires_at')->nullable();;
            $table->timestamp('activated_at')->nullable();
            $table->char('suspension_reason_code', 3)->nullable();
            $table->boolean('abnormal_behavior_flag')->default(false);
            $table->enum('source', ['system', 'promotion', 'manual'])->default('system');
            $table->text('note')->nullable();
            $table->enum('previous_status', ['unused', 'active', 'expired', 'suspended', 'upgraded'])->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('status');
            $table->index('expires_at');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
