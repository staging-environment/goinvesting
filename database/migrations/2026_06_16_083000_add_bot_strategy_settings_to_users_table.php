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
            $table->decimal('bot_buy_threshold', 5, 2)->default(-1.5);
            $table->decimal('bot_take_profit', 5, 2)->default(2.0);
            $table->decimal('bot_stop_loss', 5, 2)->default(-3.0);
            $table->decimal('bot_order_size', 12, 2)->default(500.0);
            $table->decimal('bot_max_investment', 12, 2)->default(500000.0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bot_buy_threshold',
                'bot_take_profit',
                'bot_stop_loss',
                'bot_order_size',
                'bot_max_investment'
            ]);
        });
    }
};
