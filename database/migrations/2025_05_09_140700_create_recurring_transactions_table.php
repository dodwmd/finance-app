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
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->onDelete('set null');
            $table->foreignId('chart_of_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['income', 'expense', 'transfer']);
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'annually']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_due_date');
            $table->date('last_processed_date')->nullable();
            $table->enum('status', ['active', 'paused', 'completed', 'pending'])->default('active');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
