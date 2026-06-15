<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    protected $fillable = [
        'numero', 'type', 'format_papier', 'vente_id', 'client_id',
        'client_nom', 'client_adresse', 'client_telephone',
        'user_id', 'site_id', 'sous_total', 'remise', 'tva', 'total', 'statut',
        'date_echeance', 'notes', 'imprime_le',
    ];

    protected function casts(): array
    {
        return [
            'sous_total' => 'decimal:2',
            'remise' => 'decimal:2',
            'tva' => 'decimal:2',
            'total' => 'decimal:2',
            'date_echeance' => 'date',
            'imprime_le' => 'datetime',
        ];
    }

    public function vente(): BelongsTo
    {
        return $this->belongsTo(Vente::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(FactureDetail::class)->orderBy('ordre');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'proforma' => '📋 Facture proforma',
            'devis' => '📝 Devis',
            'bon_commande' => '📋 Bon de commande',
            'bon_livraison' => '🚚 Bon de livraison',
            'ticket' => '🧾 Ticket',
            'facture_a4' => '📃 Facture A4',
            'avoir' => '↩️ Avoir',
            default => '📄 Facture',
        };
    }

    public function clientDisplayName(): string
    {
        return $this->client?->name ?? $this->client_nom ?? 'Client comptant';
    }
}
