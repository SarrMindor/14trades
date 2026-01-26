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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('mt5_account')->unique();
            $table->string('server');
            $table->string('hwid');
            $table->enum('plan', ['basic', 'normal', 'elite'])->default('basic');
            $table->date('expires_at');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_validation')->nullable();
            $table->integer('validation_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index pour la recherche rapide dans l'API
            $table->index(['mt5_account', 'server', 'hwid']);
            $table->index(['expires_at', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('licenses');
    }
};
