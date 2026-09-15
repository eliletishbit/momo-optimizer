<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationOperateur extends Model
{
    use HasFactory, HasUuids;

    // ✅ IMPORTANT : Désactiver l'incrémentation auto
    public $incrementing = false;

    // ✅ IMPORTANT : Définir le type de la clé primaire comme 'string'
    protected $keyType = 'string';

    // ✅ Nom exact de la table
    protected $table = 'operations_operateurs';

    protected $fillable = [
        'id',
        'user_id',
        'type_operation_id',
        'reseau',
        'telephone_client',
        'montant',
        'direction',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        // ✅ Ne pas caster 'id' en integer !
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeoperateur(): BelongsTo
    {
        return $this->belongsTo(TypeOperation::class, 'type_operation_id');
    }
}