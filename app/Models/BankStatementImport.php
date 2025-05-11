<?php

namespace App\Models;

use Database\Factories\BankStatementImportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method \Database\Factories\BankStatementImportFactory factory(...$parameters)
 */
class BankStatementImport extends Model
{
    /**
     * @use HasFactory<\Database\Factories\BankStatementImportFactory>
     */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Database\Factories\BankStatementImportFactory
     */
    protected static function newFactory()
    {
        return BankStatementImportFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'bank_account_id',
        'original_file_path',
        'file_hash',
        'status',
        'original_headers',
        'column_mapping',
        'total_row_count',
        'processed_row_count',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'original_headers' => 'array',
        'column_mapping' => 'array',
        'total_row_count' => 'integer',
        'processed_row_count' => 'integer',
    ];

    /**
     * Get the user that owns the bank statement import.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bank account that owns the bank statement import.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Get the staged transactions for the bank statement import.
     */
    public function stagedTransactions(): HasMany
    {
        return $this->hasMany(StagedTransaction::class);
    }
}
