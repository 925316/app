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
        Schema::create('activation_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->enum('type', ['license', 'upgrade', 'renewal']);
            $table->enum('target_license_type', ['basic', 'vip'])->nullable();
            $table->unsignedInteger('value')->nullable();
            $table->foreignId('used_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('used_by');
            $table->index('expires_at');
            $table->index(['type', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activation_keys');
    }
};
