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
        Schema::create('activation_keys', function (Blueprint $table) {
            $table->id();
            
            $table->string('key', 64)->unique();
            $table->enum('key_type', ['license', 'upgrade', 'topup', 'reset']);

            $table->enum('target_license_type', ['basic', 'vip'])->nullable();
            $table->unsignedTinyInteger('privilege_level')->default(0);

            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('license_id')->nullable()->constrained('licenses')->nullOnDelete();

            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('use_count')->default(0);

            $table->boolean('is_revoked')->default(false);
            $table->string('notes', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('accounts')->nullOnDelete();

            $table->timestamps();

            $table->index('account_id');
            $table->index('license_id');
            $table->index('created_by');
            $table->index(['key_type', 'is_revoked', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activation_keys');
    }
};
