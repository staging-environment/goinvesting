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
            $table->decimal('live_bot_buy_threshold', 5, 2)->default(-1.5)->after('bot_max_investment');
            $table->decimal('live_bot_take_profit', 5, 2)->default(2.0)->after('live_bot_buy_threshold');
            $table->decimal('live_bot_stop_loss', 5, 2)->default(-3.0)->after('live_bot_take_profit');
            $table->decimal('live_bot_order_size', 12, 2)->default(500.0)->after('live_bot_stop_loss');
            $table->decimal('live_bot_max_investment', 12, 2)->default(500000.0)->after('live_bot_order_size');
            $table->decimal('live_daily_spend_limit', 15, 2)->nullable()->default(5000.00)->after('live_bot_max_investment');
            $table->decimal('live_weekly_spend_limit', 15, 2)->nullable()->default(25000.00)->after('live_daily_spend_limit');
            $table->decimal('live_monthly_spend_limit', 15, 2)->nullable()->after('live_weekly_spend_limit');
        });

        Schema::table('bot_executions', function (Blueprint $table) {
            $table->boolean('is_paper')->default(true)->after('is_dry_run');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'live_bot_buy_threshold',
                'live_bot_take_profit',
                'live_bot_stop_loss',
                'live_bot_order_size',
                'live_bot_max_investment',
                'live_daily_spend_limit',
                'live_weekly_spend_limit',
                'live_monthly_spend_limit'
            ]);
        });

        Schema::table('bot_executions', function (Blueprint $table) {
            $table->dropColumn('is_paper');
        });
    }
};
