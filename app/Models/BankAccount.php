<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name', // Internal record name
        'account_name', // User-facing account name
        'type', // 'bank', 'credit_card', 'cash' - Broad type
        'bank_name',
        'branch_name',
        'account_type', // More specific type like 'chequing', 'savings'
        'account_number',
        'bsb',
        'currency',
        'opening_balance',
        'current_balance',
        'is_active',
        'chart_of_account_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'type' => 'string', // Though an enum, treated as string in model
    ];

    /**
     * Get the user that owns the bank account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
