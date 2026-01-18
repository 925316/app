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
        Schema::create('usage_statistics', function (Blueprint $table) {
            $table->id();

            $table->tinyInteger('stat_type')->unsigned()->nullable(false);
            $table->string('stat_key', 255)->nullable(false);
            $table->decimal('stat_value', 15, 2)->nullable(false);

            $table->timestamps();

            $table->index(['stat_type', 'stat_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_statistics');
    }
};
