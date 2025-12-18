<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usage_statistics', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('stat_type'); // 0=global, 1=user, 2=license, 3=server
            $table->string('stat_key', 255);
            $table->decimal('stat_value', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('usage_statistics');
    }
};