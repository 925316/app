<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            //$table->foreignId('license_id')->nullable()->constrained('licenses');
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_ip_address', 45)->nullable();
            $table->text('last_user_agent')->nullable();
            $table->unsignedInteger('hwid_reset_count')->default(0);
            $table->timestamp('hwid_last_reset_at')->nullable();
            $table->boolean('is_suspended')->default(false);
            $table->string('suspension_reason', 255)->nullable();
            $table->timestamp('suspended_until')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            //$table->index('license_id');
            $table->index('email_verified_at');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounts');
    }
};