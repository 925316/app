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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            
            $table->string('key', 50)->unique()->nullable(false);
            $table->tinyInteger('type')->unsigned()->nullable(false)->default(1);
            $table->tinyInteger('privilege')->unsigned()->nullable(false)->default(0);
            $table->tinyInteger('status')->unsigned()->nullable(false)->default(0);
            $table->foreignId('used_by')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();
            $table->dateTime('expires_at')->nullable(false);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('created_from_ip', 45)->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();

            $table->index('activated_at');
            $table->index(['used_by', 'status']);
            $table->index(['status', 'expires_at']);
            $table->index(['privilege', 'created_at']);
            $table->index(['expires_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
