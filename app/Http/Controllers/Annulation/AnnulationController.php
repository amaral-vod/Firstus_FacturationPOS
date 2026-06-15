<?php

namespace App\Http\Controllers\Annulation;

use App\Http\Controllers\Controller;
use App\Models\Annulation;
use App\Models\Vente;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnulationController extends Controller
{
    public function index()
    {
        $annulations = Annulation::with(['vente', 'user'])->latest()->paginate(20);
        $ventes = Vente::where('statut', 'complete')->latest()->limit(30)->get();

        return view('annulations.index', compact('annulations', 'ventes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vente_id' => 'required|exists:ventes,id',
            'motif' => 'required|string|min:10',
        ]);

        try {
            DB::transaction(function () use ($data) {
                $vente = Vente::with('details.product')->findOrFail($data['vente_id']);

                if ($vente->isAnnulee()) {
                    throw new \RuntimeException('Cette facture est déjà annulée.');
                }

                if ($vente->retours()->exists()) {
                    throw new \RuntimeException('Impossible d\'annuler une facture avec des retours.');
                }

                Annulation::create([
                    'vente_id' => $vente->id,
                    'user_id' => auth()->id(),
                    'motif' => $data['motif'],
                    'annulee_le' => now(),
                ]);

                foreach ($vente->details as $detail) {
                    $qteARemettre = $detail->quantiteRestante();
                    if ($qteARemettre > 0) {
                        StockService::adjust($detail->product, $qteARemettre, 'annulation', $vente->numero_facture, $data['motif']);
                    }
                }

                $vente->update(['statut' => 'annulee']);

                ActivityLogger::log('annulation', 'annulations', "Annulation facture {$vente->numero_facture}", [
                    'vente_id' => $vente->id,
                    'motif' => $data['motif'],
                ]);
            });

            return back()->with('success', '🚫 Facture annulée avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', '❌ '.$e->getMessage());
        }
    }
}
