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
        Schema::table('staged_transactions', function (Blueprint $table) {
            $tableName = 'staged_transactions';
            $columnName = 'matched_transaction_id';

            if (! Schema::hasColumn($tableName, $columnName)) {
                // Add column with foreign key and implicit index
                $table->foreignId($columnName)
                    ->nullable()
                    ->after('status') // Ensure it's placed after the status column
                    ->constrained('transactions')
                    ->onDelete('set null');
            } else {
                // If column exists, ensure it's nullable (if that's desired state and might not be)
                // DB::statement("ALTER TABLE {$tableName} MODIFY COLUMN {$columnName} BIGINT UNSIGNED NULL");
                // Also, ensure the foreign key exists if column was already there without it (more complex to check robustly without DBAL)
                // For now, we assume if column exists, prior attempts handled FK or it's not critical to re-add here if this is re-run
            }

            // Laravel's foreignId()->constrained() typically creates an index automatically.
            // If a separate explicit index is strictly needed and its check is problematic:
            // try { $table->index([$columnName]); } catch (\Exception $e) { /* Log or ignore if index already exists */ }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staged_transactions', function (Blueprint $table) {
            $tableName = 'staged_transactions';
            $columnName = 'matched_transaction_id';

            // Check if column exists before attempting to drop foreign key and column
            if (Schema::hasColumn($tableName, $columnName)) {
                // Drop foreign key constraint (Laravel attempts to find by column name)
                // This might require $table->dropForeign('staged_transactions_matched_transaction_id_foreign'); by explicit name if default finding fails.
                $table->dropForeign([$columnName]);

                // Drop the column itself
                $table->dropColumn($columnName);
            }
            // Note: dropIndex might be needed if an explicit index was created separately from the FK constraint.
            // If foreignId()->constrained() created the index, dropping the foreign key and column usually handles it.
        });
    }
};
