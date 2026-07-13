<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->decimal('highest_price', 16, 4)->nullable()->after('price');
            $table->integer('dca_level')->default(0)->after('highest_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropColumn(['highest_price', 'dca_level']);
        });
    }
};
