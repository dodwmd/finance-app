<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add the new category_id column (nullable initially)
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('type');
        });

        // 2. Migrate existing data - find appropriate category IDs based on string names
        // This will be done in a separate command for better control

        // 3. Drop the old category column
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // 1. Re-add the category string column
            $table->string('category')->after('type');
            
            // 2. Data migration would be lost in a rollback
            
            // 3. Remove the category_id column
            $table->dropColumn('category_id');
        });
    }
};
