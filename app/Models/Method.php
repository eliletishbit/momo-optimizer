<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Method extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'code',
        'category',
        'country_code',
        'logo_url',
        'color_primary',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $method) {
            $method->id ??= (string) Str::uuid();

            $countryCode = strtoupper(trim((string) ($method->country_code ?? '')));
            if ($countryCode !== '') {
                Country::firstOrCreate(
                    ['code' => $countryCode],
                    [
                        'name' => $method->country?->name ?? $countryCode,
                        'currency' => $method->currency ?? 'XOF',
                        'is_active' => true,
                    ]
                );
            }
        });
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    public function userMethods(): HasMany
    {
        return $this->hasMany(UserMethod::class);
    }

    public function receiptFees(): HasMany
    {
        return $this->hasMany(ReceiptFee::class);
    }

    public function sendingFees(): HasMany
    {
        return $this->hasMany(SendingFee::class);
    }

    public function optimizationHistory(): HasMany
    {
        return $this->hasMany(OptimizationHistory::class, 'selected_method_id');
    }
}
