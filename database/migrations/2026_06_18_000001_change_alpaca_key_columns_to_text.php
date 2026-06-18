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
        Schema::table('users', function (Blueprint $table) {
            $table->text('alpaca_key_id')->nullable()->change();
            $table->text('alpaca_live_key_id')->nullable()->change();
            $table->text('alpaca_live_secret_key')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('alpaca_key_id', 255)->nullable()->change();
            $table->string('alpaca_live_key_id', 255)->nullable()->change();
            $table->string('alpaca_live_secret_key', 255)->nullable()->change();
        });
    }
};
