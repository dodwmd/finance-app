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
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Attempt to restore the column in a similar position if needed, adjust 'after' as per your original structure
            // Adding a comment to note its original purpose might be useful.
            $table->string('name')->after('user_id')->nullable()->comment('Internal record name, restored from migration rollback.');
        });
    }
};
