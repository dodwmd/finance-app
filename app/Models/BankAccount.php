<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    /**
     * @use HasFactory<\Database\Factories\BankAccountFactory>
     */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
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

    /**
     * Get the transactions for the bank account.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Set the BSB, stripping non-numeric characters.
     */
    public function setBsbAttribute(?string $value): void
    {
        if ($value === null) {
            $this->attributes['bsb'] = null;
        } else {
            $this->attributes['bsb'] = preg_replace('/[\s\-]+/', '', $value);
        }
    }

    /**
     * Get the BSB formatted for display (e.g., XXX-XXX).
     */
    public function getFormattedBsbAttribute(): ?string
    {
        $bsb = $this->attributes['bsb'];
        if ($bsb && strlen($bsb) === 6 && ctype_digit($bsb)) {
            return substr($bsb, 0, 3).'-'.substr($bsb, 3, 3);
        }

        return $bsb; // Return original if not a 6-digit string or null
    }
}
