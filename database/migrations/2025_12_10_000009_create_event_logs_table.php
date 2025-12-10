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

            $table->string('event_type', 50);
            $table->string('event_subtype', 50)->nullable();

            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('license_id')->nullable()->constrained('licenses')->nullOnDelete();
            $table->foreignId('performed_by_id')->nullable()->constrained('accounts')->nullOnDelete();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('user_agent_hash', 64)->nullable();

            $table->unsignedTinyInteger('risk_score')->nullable();

            $table->json('details')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();


            $table->index('performed_by_id');
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
