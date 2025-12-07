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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->unsignedTinyInteger('privilege_level')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('register_ip', 45)->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('banned_at')->nullable();
            $table->string('suspension_reason', 255)->nullable();
            $table->string('migrated_id', 255)->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
            $table->index('banned_at');
            $table->index('privilege_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
