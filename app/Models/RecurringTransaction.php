<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class RecurringTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'description',
        'amount',
        'type',
        'category_id',
        'frequency',
        'start_date',
        'end_date',
        'next_due_date',
        'last_processed_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_due_date' => 'date',
        'last_processed_date' => 'date',
    ];

    /**
     * Get the user that owns the recurring transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that this recurring transaction belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Calculate the next occurrence date based on frequency and current date.
     */
    public function calculateNextDueDate(Carbon $fromDate = null): Carbon
    {
        $fromDate = $fromDate ?? $this->next_due_date ?? $this->start_date;
        
        return match ($this->frequency) {
            'daily' => Carbon::parse($fromDate)->addDay(),
            'weekly' => Carbon::parse($fromDate)->addWeek(),
            'biweekly' => Carbon::parse($fromDate)->addWeeks(2),
            'monthly' => Carbon::parse($fromDate)->addMonth(),
            'quarterly' => Carbon::parse($fromDate)->addMonths(3),
            'annually' => Carbon::parse($fromDate)->addYear(),
            default => Carbon::parse($fromDate)->addMonth(),
        };
    }

    /**
     * Check if the recurring transaction is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               ($this->end_date === null || $this->end_date >= now()->toDateString());
    }

    /**
     * Check if a transaction should be generated for the current date.
     */
    public function shouldGenerateTransaction(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->next_due_date <= now()->toDateString();
    }
}
