<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\OperatorVirtualSubaccount;

class OperatorProfile extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'operator_profiles';

    protected $fillable = [
        'user_id',
        'business_name',
        'phone',
        'address',
        'city',
        'is_active',
        'approved_at',
        'approved_by',
        'caisse_physique',
        'caisse_virtuelle',
        'caisse_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
        'caisse_physique' => 'decimal:2',
        'caisse_virtuelle' => 'decimal:2',
        'caisse_date' => 'date',
    ];

    /**
     * ✅ Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ✅ Relation avec l'approbateur
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * ✅ Vérifier si l'opérateur est actif
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->approved_at !== null;
    }

    /**
     * ✅ Récupérer les opérations de la journée
     */
    public function getTodayOperations(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->user->operations()
            ->whereDate('created_at', today())
            ->get();
    }

    /**
     * ✅ Calculer le solde de la caisse physique
     */
    public function getCaissePhysiqueSolde(): float
    {
        $operations = $this->getTodayOperations();

        // Entrant : le client retire (sortant)
        $entrant = $operations->where('direction', 'sortant')->sum('montant');
        // Sortant : le client dépose (entrant)
        $sortant = $operations->where('direction', 'entrant')->sum('montant');

        return $this->caisse_physique + $entrant - $sortant;
    }

    /**
     * ✅ Calculer le solde de la caisse virtuelle
     */
    public function getCaisseVirtuelleSolde(): float
    {
        $operations = $this->getTodayOperations();

        // Entrant : le client reçoit (entrant)
        $entrant = $operations->where('direction', 'entrant')->sum('montant');
        // Sortant : le client envoie (sortant)
        $sortant = $operations->where('direction', 'sortant')->sum('montant');

        return $this->caisse_virtuelle + $entrant - $sortant;
    }

    /**
     * ✅ Mettre à jour les caisses pour une nouvelle journée
     */
    public function updateCaissesForNewDay(): void
    {
        $today = today();

        if ($this->caisse_date === null || $this->caisse_date->lt($today)) {
            $this->caisse_physique = $this->getCaissePhysiqueSolde();
            $this->caisse_virtuelle = $this->getCaisseVirtuelleSolde();
            $this->caisse_date = $today;
            $this->save();
        }
    }



    ///////////////////////////////// relations pour sous comptes virtuels 
    // Dans OperatorProfile.php



// Relation
public function virtualSubaccounts(): HasMany
{
    return $this->hasMany(OperatorVirtualSubaccount::class);
}

// Méthode pour recalculer la caisse virtuelle globale (somme des sous-comptes)
public function recalculateVirtualCaisse(): void
{
    $total = $this->virtualSubaccounts()->sum('solde');
    $this->caisse_virtuelle = $total;
    $this->save();
}

// Méthode pour initialiser les sous-comptes pour un nouvel opérateur
public function initializeVirtualSubaccounts(): void
{
    $reseaux = ['MTN', 'Moov', 'Celtiis', 'Orange'];
    foreach ($reseaux as $reseau) {
        $this->virtualSubaccounts()->create([
            'reseau' => $reseau,
            'solde' => 0,
        ]);
    }
}





}