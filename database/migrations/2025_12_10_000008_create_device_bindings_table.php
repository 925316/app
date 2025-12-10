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
        Schema::create('device_bindings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('license_id')->constrained('licenses')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('account_device_id')->nullable()->constrained('account_devices')->nullOnDelete();

            $table->string('hwid_hash', 64);
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('user_agent_hash', 64)->nullable();

            $table->char('country_code', 2)->nullable();

            $table->boolean('is_active')->default(true);
            $table->enum('binding_type', ['initial', 'reset', 'auto', 'manual'])->default('initial');

            $table->unsignedInteger('regen_count_at_binding')->default(0);

            $table->timestamp('unbound_at')->nullable();
            $table->string('unbind_reason', 255)->nullable();

            $table->timestamps();


            $table->index('binding_type');
            $table->index(['license_id', 'is_active', 'created_at']);
            $table->index(['account_id', 'created_at']);
            $table->index(['hwid_hash', 'created_at']);
            $table->index(['account_id', 'license_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_bindings');
    }
};
