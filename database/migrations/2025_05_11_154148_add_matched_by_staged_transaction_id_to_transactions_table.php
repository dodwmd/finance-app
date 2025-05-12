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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('matched_by_staged_transaction_id')
                ->nullable()
                ->constrained('staged_transactions')
                ->onDelete('set null'); // Or 'cascade' if a matched staged transaction deletion should delete the real one (less likely)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['matched_by_staged_transaction_id']);
            $table->dropColumn('matched_by_staged_transaction_id');
        });
    }
};
