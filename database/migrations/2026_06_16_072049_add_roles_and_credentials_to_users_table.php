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
            $table->string('role')->default('investor')->after('password');
            $table->decimal('daily_spend_limit', 15, 2)->nullable()->default(5000.00)->after('role');
            $table->decimal('weekly_spend_limit', 15, 2)->nullable()->default(25000.00)->after('daily_spend_limit');
            $table->string('alpaca_key_id')->nullable()->after('weekly_spend_limit');
            $table->text('alpaca_secret_key')->nullable()->after('alpaca_key_id');
            $table->string('alpaca_account_id')->nullable()->after('alpaca_secret_key');
            $table->boolean('alpaca_is_paper')->default(true)->after('alpaca_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'daily_spend_limit',
                'weekly_spend_limit',
                'alpaca_key_id',
                'alpaca_secret_key',
                'alpaca_account_id',
                'alpaca_is_paper'
            ]);
        });
    }
};
