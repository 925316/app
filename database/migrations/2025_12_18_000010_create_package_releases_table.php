<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('package_releases', function (Blueprint $table) {
            $table->id();
            $table->string('version', 50)->unique();
            $table->enum('release_channel', ['stable', 'dev'])->default('stable');
            $table->string('download_url', 255);
            $table->char('checksum_sha256', 64)->nullable();
            $table->text('changelog')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('package_releases');
    }
};