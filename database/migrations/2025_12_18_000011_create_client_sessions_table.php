<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('client_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_token', 128)->unique();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('client_version', 50);
            $table->string('language', 10)->default('en');
            $table->json('session_data')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamps();

            $table->index('last_heartbeat_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_sessions');
    }
};