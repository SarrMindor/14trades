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
        Schema::table('mt5_accounts', function (Blueprint $table) {
            $table->string('hwid', 64)->nullable()->after('server');
            $table->timestamp('last_heartbeat')->nullable()->after('hwid');
            $table->boolean('is_connected')->default(false)->after('last_heartbeat');
            
            // Index pour optimiser les requêtes
            $table->index(['account_number', 'server', 'hwid'], 'mt5_account_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mt5_accounts', function (Blueprint $table) {
            // Supprimer l'index d'abord
            $table->dropIndex('mt5_account_lookup');
            
            // Ensuite supprimer les colonnes
            $table->dropColumn(['hwid', 'last_heartbeat', 'is_connected']);
        });
    }
};