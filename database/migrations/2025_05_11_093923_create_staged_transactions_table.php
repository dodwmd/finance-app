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
        Schema::create('staged_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            $table->uuid('import_batch_id')->index(); // To group transactions from the same import

            $table->date('transaction_date');
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2); // Positive for deposits, negative for withdrawals
            $table->string('type', 50)->nullable()->comment('e.g., debit, credit, or more specific if available'); // Could be 'debit' or 'credit'

            $table->text('original_raw_data')->nullable(); // Store the original row from CSV/QIF/OFX
            $table->string('data_hash')->unique()->comment('Hash of key row data to prevent exact duplicates from source');

            $table->string('status', 50)->default('pending_review')->index(); // e.g., pending_review, matched, imported, ignored, error

            $table->foreignId('suggested_category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('matched_transaction_id')->nullable()->constrained('transactions')->onDelete('set null');

            $table->text('notes')->nullable(); // For user or system notes during review
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staged_transactions');
    }
};
