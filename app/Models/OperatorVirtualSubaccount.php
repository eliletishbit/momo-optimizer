<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatorVirtualSubaccount extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'operator_profile_id',
        'reseau',
        'solde',
    ];

    protected $casts = [
        'solde' => 'decimal:2',
    ];

    public function operatorProfile(): BelongsTo
    {
        return $this->belongsTo(OperatorProfile::class);
    }
}