<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// For default user_id
// For matched_transaction_id relationship

/**
 * @method static \Illuminate\Database\Eloquent\Factories\Factory<static> factory(...$parameters)
 */
class StagedTransaction extends Model
{
    /**
     * @use HasFactory<\Database\Factories\StagedTransactionFactory>
     */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Database\Factories\StagedTransactionFactory
     */
    protected static function newFactory()
    {
        return \Database\Factories\StagedTransactionFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'bank_account_id',
        'bank_statement_import_id',
        'transaction_date',
        'description',
        'amount',
        'type', // 'credit' or 'debit'
        'original_raw_data',
        'data_hash',
        'status', // e.g., pending_review, imported, ignored, potential_duplicate
        'suggested_category_id',
        'approved_by_user_id',
        'approved_at',
        'ignored_by_user_id',
        'ignored_at',
        'matched_transaction_id',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'original_raw_data' => 'array', // If storing CSV row as JSON
        'ignored_at' => 'datetime',
    ];

    /**
     * Get the user that owns the staged transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bank account associated with the staged transaction.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Get the bank statement import associated with the staged transaction.
     */
    public function bankStatementImport(): BelongsTo
    {
        return $this->belongsTo(BankStatementImport::class);
    }

    /**
     * Get the matched transaction for the staged transaction.
     */
    public function matchedTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'matched_transaction_id');
    }

    /**
     * Get the suggested category for the staged transaction.
     */
    public function suggestedCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'suggested_category_id');
    }
}
