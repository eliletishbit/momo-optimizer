<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeOperation extends Model
{
    use HasFactory, HasUuids;

    // ✅ Nom exact de la table dans la migration
    protected $table = 'types_operation';

    protected $fillable = [
        'nom',
        'code',
    ];

    public function operations(): HasMany
    {
        return $this->hasMany(OperationOperateur::class, 'type_operation_id');
    }
}