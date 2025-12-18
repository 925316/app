<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ip_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('endpoint', 255);
            $table->unsignedInteger('request_count')->default(1);
            $table->timestamp('last_request_at');
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('blocked_until')->nullable();
            $table->string('block_reason', 255)->nullable();
            $table->timestamps();

            $table->index('ip_address');
            $table->index('endpoint');
            $table->index(['ip_address', 'endpoint']);
            $table->index(['is_blocked', 'blocked_until']);
            $table->index(['last_request_at', 'endpoint']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ip_rate_limits');
    }
};