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

            $table->string('license_key', 64)->unique();

            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->enum('license_type', ['basic', 'vip']);
            $table->unsignedTinyInteger('license_tier')->comment('1=basic, 2=vip');

            $table->enum('status', ['unused', 'active', 'suspended', 'expired', 'upgraded', 'revoked'])->default('unused');

            $table->unsignedBigInteger('device_binding_id')->nullable();
            $table->foreignId('account_device_id')->nullable()->constrained('account_devices')->nullOnDelete();

            $table->timestamp('hwid_bound_at')->nullable();
            $table->timestamp('hwid_reset_at')->nullable();

            $table->string('activation_key_used', 255)->nullable();

            $table->timestamp('expires_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();

            $table->string('suspension_reason', 255)->nullable();
            $table->string('auto_suspend_reason', 255)->nullable();

            $table->foreignId('upgraded_to_id')->nullable()->constrained('licenses')->nullOnDelete();
            $table->string('created_from_ip', 45)->nullable();
            $table->unsignedInteger('total_activations')->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('device_binding_id');
            $table->index('account_device_id');
            $table->index('activated_at');
            $table->index(['account_id', 'status']);
            $table->index(['status', 'expires_at']);
            $table->index(['license_type', 'created_at']);
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
