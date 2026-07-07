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
        Schema::create('api_signing_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key_id')->unique();
            $table->string('algorithm')->default('RSA-2048-SHA256');
            $table->text('public_key');
            $table->string('public_key_fingerprint')->unique();
            $table->string('private_key_path')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('rotated_at')->nullable();
            $table->timestamp('retired_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('accounts')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_signing_keys');
    }
};
