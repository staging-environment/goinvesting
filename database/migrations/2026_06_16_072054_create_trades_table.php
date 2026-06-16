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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bot_execution_id')->nullable()->constrained()->onDelete('set null');
            $table->string('symbol');
            $table->decimal('qty', 15, 6);
            $table->decimal('price', 15, 4);
            $table->string('side'); // buy or sell
            $table->string('status')->default('filled');
            $table->boolean('is_dry_run')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
