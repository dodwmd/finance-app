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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('account_code');
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense', 'costofgoodssold']);
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_direct_posting')->default(true);
            $table->string('system_account_tag')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'account_code']);
            // A system tag should be unique for a user if set, but allow multiple nulls
            // So, a standard unique constraint on a nullable column might not work as expected across all DBs
            // For now, let's assume if system_account_tag is set, it must be unique for the user.
            // If this becomes an issue with specific DBs allowing multiple nulls in unique constraints,
            // we might need a more complex solution or a partial index (if supported and needed).
            $table->unique(['user_id', 'system_account_tag'], 'user_system_tag_unique')->whereNotNull('system_account_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            // To be perfectly reversible, drop constraints first if they exist
            // This depends on how you named your unique constraints
            // $table->dropUnique('user_system_tag_unique'); // Example
        });
        Schema::dropIfExists('chart_of_accounts');
    }
};
