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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Factory default name, e.g., 'Little Ltd Account'
            $table->enum('type', ['bank', 'credit_card', 'cash']); // Factory default type, e.g., 'cash'

            // Columns specifically set or expected by BankAccountSeeder
            $table->string('account_name'); // User-defined name, e.g., 'Main Chequing Account'
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('account_type')->nullable(); // e.g., 'chequing', 'savings'
            $table->string('currency', 10)->default('CAD');
            $table->boolean('is_active')->default(true);

            $table->foreignId('chart_of_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');

            $table->string('bsb')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('current_balance', 15, 2)->default(0.00); // Will be updated by transactions
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
