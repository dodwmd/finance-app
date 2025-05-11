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
        Schema::create('bank_statement_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            $table->string('original_file_path'); // Stores the path to the original file
            $table->string('file_hash')->nullable(); // For duplicate file detection
            $table->string('status')->default('pending_mapping'); // e.g., pending_mapping, awaiting_review, processing, completed, failed
            $table->json('original_headers')->nullable(); // Store the actual first row (headers) from CSV
            $table->json('column_mapping')->nullable(); // Stores the active column mapping (user-defined or initial)
            $table->integer('total_row_count')->nullable(); // Total rows in the imported file (excluding header)
            $table->integer('processed_row_count')->default(0); // Number of rows successfully staged
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_statement_imports');
    }
};
