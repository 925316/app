<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->unsignedTinyInteger('event_level')->default(0); // 0=info, 1=warn, 2=error
            $table->foreignId('account_id')->nullable()->constrained('accounts');
            $table->foreignId('license_id')->nullable()->constrained('licenses');
            $table->string('ip_address', 45)->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('accounts');
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index('actor_id');
            $table->index(['event_type', 'created_at']);
            $table->index(['account_id', 'created_at']);
            $table->index(['license_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_logs');
    }
};