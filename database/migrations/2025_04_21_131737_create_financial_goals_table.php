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
        Schema::create('financial_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->decimal('target_amount', 10, 2);
            $table->decimal('current_amount', 10, 2)->default(0.00);
            $table->string('type'); // saving, debt_repayment, investment, etc.
            $table->date('start_date');
            $table->date('target_date');
            $table->text('description')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Add index for faster queries
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'is_completed']);
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_goals');
    }
};
