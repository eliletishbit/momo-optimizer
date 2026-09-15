<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OptimizationHistory extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'optimization_history';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'selected_method_id',
        'total_fee',
        'savings',
        'alternatives',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'total_fee' => 'decimal:2',
        'savings' => 'decimal:2',
        'alternatives' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $history) {
            $history->id ??= (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function selectedMethod(): BelongsTo
    {
        return $this->belongsTo(Method::class, 'selected_method_id');
    }
}
