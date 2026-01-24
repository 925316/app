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
        Schema::create('account_devices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')
                ->constrained('accounts')
                ->onDelete('cascade');
            $table->string('hwid_hash', 64)->nullable();
            $table->binary('ip_address', 16)->nullable(false);
            $table->char('country_code', 2)->nullable();
            $table->json('characteristics')->nullable();
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamp('bound_at')->nullable();
            $table->timestamp('unbound_at')->nullable();

            $table->timestamps();

            $table->unique(['account_id', 'hwid_hash']);
            $table->index(['account_id', 'last_seen_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_devices');
    }
};
