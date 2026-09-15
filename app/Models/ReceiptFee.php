<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ReceiptFee extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'receipt_fees';

    protected $fillable = [
        'method_id',
         'method_name',
        'country_code',
        'min_amount',
        'max_amount',
        'fee_amount',
        'fee_type',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $fee) {
            $fee->id ??= (string) Str::uuid();
        });
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(Method::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }
}
