a<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('account_number');
            $table->string('ticket')->unique();
            $table->string('symbol');
            $table->enum('type', ['buy', 'sell']);
            $table->decimal('volume', 10, 2);
            $table->decimal('open_price', 15, 5);
            $table->decimal('close_price', 15, 5)->nullable();
            $table->decimal('stop_loss', 15, 5)->nullable();
            $table->decimal('take_profit', 15, 5)->nullable();
            $table->decimal('swap', 10, 2)->default(0);
            $table->decimal('commission', 10, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->timestamp('open_time');
            $table->timestamp('close_time')->nullable();
            $table->enum('result', ['profit', 'loss', 'breakeven'])->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            // Index pour les recherches fréquentes
            $table->index(['user_id', 'account_number']);
            $table->index(['open_time', 'close_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
