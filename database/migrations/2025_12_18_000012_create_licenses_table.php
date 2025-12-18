<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->unsignedTinyInteger('type')->default(1); // 1=base, 2=upgrade
            $table->foreignId('used_by')->nullable()->constrained('accounts');
            $table->unsignedTinyInteger('privilege')->default(0);
            $table->unsignedTinyInteger('status')->default(0);
            $table->datetime('expires_at');
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

        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('license_id')->nullable()->after('password');
            $table->foreign('license_id')->references('id')->on('licenses')->nullOnDelete();

            $table->index('license_id');
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['license_id']);
            $table->dropColumn('license_id');
        });

        Schema::dropIfExists('licenses');
    }
};
