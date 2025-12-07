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
        Schema::create('activation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('operation', ['activate', 'bind', 'unbind', 'reset_hwid', 'renew', 'upgrade']);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('performed_at')->useCurrent();
            
            $table->index('license_id');
            $table->index('user_id');
            $table->index('performed_at');
            $table->index(['license_id', 'performed_at']);
            $table->index(['user_id', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activation_logs');
    }
};
