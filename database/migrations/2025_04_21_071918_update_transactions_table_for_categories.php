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
        // 1. Add the new category_id column (nullable initially)
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('type');
            }
        });

        // 2. Only try to drop the old category column if it exists
        if (Schema::hasColumn('transactions', 'category')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // 1. Re-add the category string column with a default empty value
            if (! Schema::hasColumn('transactions', 'category')) {
                $table->string('category')->default('')->after('type');
            }

            // 2. Data migration would be lost in a rollback

            // 3. Remove the category_id column if it exists
            if (Schema::hasColumn('transactions', 'category_id')) {
                $table->dropColumn('category_id');
            }
        });
    }
};
