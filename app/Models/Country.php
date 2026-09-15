<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Country extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'code',
        'currency',
        'flag_url',
        'is_active',
    ];

    protected $casts = [
    'is_active' => 'boolean',
];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $country) {
            $country->id ??= (string) Str::uuid();
        });
    }

    public function methods(): HasMany
    {
        return $this->hasMany(Method::class, 'country_code', 'code');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'country_code', 'code');
    }

    public function receiptFees(): HasMany
    {
        return $this->hasMany(ReceiptFee::class, 'country_code', 'code');
    }

    public function sendingFees(): HasMany
    {
        return $this->hasMany(SendingFee::class, 'country_code', 'code');
    }
}
