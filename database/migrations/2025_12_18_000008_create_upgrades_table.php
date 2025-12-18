<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('license_from')->constrained('licenses');
            $table->foreignId('license_to')->constrained('licenses');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('account_id');
            $table->index('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('upgrades');
    }
};