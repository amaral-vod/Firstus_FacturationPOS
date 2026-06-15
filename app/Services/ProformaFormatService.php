<?php

namespace App\Services;

use App\Models\Facture;

class ProformaFormatService
{
    public static function resolve(Facture $facture): string
    {
        if ($facture->format_papier && $facture->format_papier !== 'auto') {
            return $facture->format_papier;
        }

        $lignes = self::lignes($facture);
        $nbLignes = $lignes->count();
        $totalArticles = $lignes->sum('quantite');
        $notesLongues = strlen($facture->notes ?? '') > 80;
        $clientComplet = $facture->client_id || $facture->client_nom;

        if ($nbLignes <= 2 && $totalArticles <= 3 && ! $notesLongues && ! $clientComplet) {
            return 'ticket';
        }

        if ($nbLignes <= 5 && $totalArticles <= 10 && ! $notesLongues) {
            return 'a5';
        }

        return 'a4';
    }

    public static function lignes(Facture $facture)
    {
        if ($facture->relationLoaded('details') && $facture->details->isNotEmpty()) {
            return $facture->details;
        }

        if ($facture->details()->exists()) {
            return $facture->details;
        }

        if ($facture->vente) {
            return $facture->vente->details->map(fn ($d) => (object) [
                'designation' => $d->product->name,
                'quantite' => $d->quantite,
                'prix_unitaire' => $d->prix_unitaire,
                'total_ligne' => $d->total_ligne,
            ]);
        }

        return collect();
    }

    public static function label(string $format): string
    {
        return match ($format) {
            'a5' => 'A5',
            'ticket' => 'Ticket 80 mm',
            default => 'A4',
        };
    }
}
