<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vente extends Model
{
    protected $fillable = [
        'numero_facture', 'user_id', 'client_id', 'caisse_session_id', 'site_id',
        'sous_total', 'remise', 'total',
        'montant_paye', 'monnaie', 'statut', 'mode_paiement', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'sous_total' => 'decimal:2',
            'remise' => 'decimal:2',
            'total' => 'decimal:2',
            'montant_paye' => 'decimal:2',
            'monnaie' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function clientLabel(): string
    {
        if ($this->client) {
            return $this->client->name;
        }

        if ($this->notes && str_starts_with($this->notes, 'Client: ')) {
            return trim(substr($this->notes, 8));
        }

        return 'Client comptoir';
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailVente::class);
    }

    public function retours(): HasMany
    {
        return $this->hasMany(Retour::class);
    }

    public function annulation(): HasOne
    {
        return $this->hasOne(Annulation::class);
    }

    public function isAnnulee(): bool
    {
        return $this->statut === 'annulee';
    }
}
