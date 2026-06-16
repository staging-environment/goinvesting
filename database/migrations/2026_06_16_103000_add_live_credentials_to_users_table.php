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
            $table->string('alpaca_live_key_id')->nullable()->after('alpaca_secret_key');
            $table->string('alpaca_live_secret_key')->nullable()->after('alpaca_live_key_id');
            $table->string('alpaca_live_account_id')->nullable()->after('alpaca_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'alpaca_live_key_id',
                'alpaca_live_secret_key',
                'alpaca_live_account_id'
            ]);
        });
    }
};
