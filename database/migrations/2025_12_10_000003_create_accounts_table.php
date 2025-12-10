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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');

            $table->enum('privilege_level', ['user', 'basic', 'vip'])->default('user');
            $table->string('preferred_language', 10)->default('en');
            $table->string('last_ip_address', 45)->nullable();
            $table->text('last_user_agent')->nullable();

            $table->unsignedInteger('hwid_reset_count')->default(0);
            $table->timestamp('hwid_last_reset_at')->nullable();

            $table->string('suspension_reason', 255)->nullable();
            $table->timestamp('suspended_until')->nullable();

            $table->string('migrated_from', 255)->nullable();

            $table->timestamp('email_verified_at')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('email_verified_at');
            $table->index('created_at');
            $table->index(['privilege_level', 'suspended_until']);
            $table->index(['suspended_until', 'suspension_reason']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();

            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('accounts');
    }
};
