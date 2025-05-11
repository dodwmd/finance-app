<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    /**
     * @use HasFactory<\Database\Factories\ChartOfAccountFactory>
     */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'account_code',
        'name',
        'type',
        'description',
        'parent_id',
        'is_active',
        'allow_direct_posting',
        'system_account_tag',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'allow_direct_posting' => 'boolean',
    ];

    /**
     * Get the user that owns the chart of account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent account of this account.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Get the child accounts of this account.
     */
    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    // Optional: A scope to get only active accounts
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Optional: A scope to get only accounts allowing direct posting
    public function scopeDirectPosting($query)
    {
        return $query->where('allow_direct_posting', true);
    }
}
