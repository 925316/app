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
        Schema::create('heartbeats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->onDelete('cascade');
            $table->string('hwid', 255);
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('client_version', 50);
            $table->timestamp('received_at')->useCurrent();
            $table->timestamps();
            
            $table->index('license_id');
            $table->index('received_at');
            $table->index('hwid');
            $table->index(['license_id', 'received_at']);
            $table->index(['received_at', 'hwid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heartbeats');
    }
};
