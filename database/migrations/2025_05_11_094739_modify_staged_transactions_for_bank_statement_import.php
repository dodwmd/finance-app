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
            // Add the new foreign key column. Make it nullable for now to handle existing rows.
            // In a real scenario, you might backfill this data before making it non-nullable.
            $table->foreignId('bank_statement_import_id')->nullable()->constrained('bank_statement_imports')->onDelete('cascade')->after('bank_account_id');

            // Drop the old column if it exists and you're sure no data needs to be migrated from it / it's truly obsolete.
            // Check if the column exists before trying to drop it, to make the migration more robust.
            if (Schema::hasColumn('staged_transactions', 'import_batch_id')) {
                // For SQLite, it's crucial to drop indexes before dropping the column.
                // The error message specified this index name.
                $table->dropIndex('staged_transactions_import_batch_id_index');
                $table->dropColumn('import_batch_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staged_transactions', function (Blueprint $table) {
            // Add back the old column. Its original type was likely string if it held UUIDs.
            // Make it nullable as we don't know its original nullability for certain without checking old migration.
            $table->string('import_batch_id')->nullable()->after('bank_account_id');
            // Re-add the index that was dropped in the up() method.
            $table->index('import_batch_id', 'staged_transactions_import_batch_id_index');

            // Drop the foreign key and the column
            // Need to get the foreign key name right, usually it's table_column_foreign
            // $table->dropForeign(['bank_statement_import_id']); // Schema::dropForeign doesn't take array in older Laravel
            if (Schema::hasColumn('staged_transactions', 'bank_statement_import_id')) {
                // Manually find and drop foreign key constraint name if default isn't working, or use:
                // $foreignKeys = array_map(function ($key) {
                //    return $key->getName();
                // }, Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('staged_transactions'));
                // if (in_array('staged_transactions_bank_statement_import_id_foreign', $foreignKeys)) { ... }
                $table->dropForeign(['bank_statement_import_id']); // Assuming default naming convention
                $table->dropColumn('bank_statement_import_id');
            }
        });
    }
};
