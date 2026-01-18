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
        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 255)->nullable(false);
            $table->tinyInteger('event_level')->unsigned()->nullable(false)->default(0);
            $table->foreignId('account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();
            $table->foreignId('license_id')
                ->nullable()
                ->constrained('licenses')
                ->nullOnDelete();
            $table->binary('ip_address', 16)->nullable();
            $table->foreignId('actor_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();
            $table->json('details')->nullable();

            $table->timestamps();

            $table->index('actor_id');
            $table->index(['event_type', 'created_at']);
            $table->index(['account_id', 'created_at']);
            $table->index(['license_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_logs');
    }
};
