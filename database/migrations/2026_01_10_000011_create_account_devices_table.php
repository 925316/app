<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        Schema::create('account_devices', function (Blueprint $table) use ($driver) {
            $table->id();

            $table->foreignId('account_id')
                ->constrained('accounts')
                ->onDelete('cascade');
            $table->string('hwid_hash', 64)->nullable();
            $table->binary('ip_address', 16)->nullable(false);
            $table->char('country_code', 2)->nullable();
            $table->json('characteristics')->nullable();
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamp('bound_at')->nullable();
            $table->timestamp('unbound_at')->nullable();

            if ($driver === 'mysql') {
                $table->unsignedBigInteger('active_binding_account_id')
                    ->nullable()
                    ->storedAs('CASE WHEN bound_at IS NOT NULL AND unbound_at IS NULL THEN account_id ELSE NULL END');
            }

            $table->timestamps();

            $table->unique(['account_id', 'hwid_hash']);
            $table->index(['account_id', 'last_seen_at']);
        });

        if ($driver === 'mysql') {
            DB::statement('CREATE UNIQUE INDEX account_devices_active_binding_unique ON account_devices (active_binding_account_id)');

            return;
        }

        DB::statement('CREATE UNIQUE INDEX account_devices_active_binding_unique ON account_devices (account_id) WHERE bound_at IS NOT NULL AND unbound_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_devices');
    }
};
