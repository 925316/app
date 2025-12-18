<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('account_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->string('hwid_hash', 64);
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45);
            $table->char('country_code', 2)->nullable();
            $table->json('device_fingerprint')->nullable();
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamp('bound_at')->nullable();
            $table->timestamp('unbound_at')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'hwid_hash']);
            $table->index(['account_id', 'last_seen_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_devices');
    }
};