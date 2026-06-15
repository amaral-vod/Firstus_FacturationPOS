<?php

namespace App\Http\Controllers\Retour;

use App\Http\Controllers\Controller;
use App\Models\DetailVente;
use App\Models\Retour;
use App\Models\RetourDetail;
use App\Models\Vente;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetourController extends Controller
{
    public function index()
    {
        $retours = Retour::with(['vente', 'user'])->latest()->paginate(20);

        return view('retours.index', compact('retours'));
    }

    public function create(Request $request)
    {
        $vente = null;

        if ($request->filled('vente_id')) {
            $vente = Vente::with('details.product')->findOrFail($request->vente_id);
        }

        $ventes = Vente::whereIn('statut', ['complete', 'partiellement_retournee'])
            ->latest()
            ->limit(50)
            ->get();

        return view('retours.create', compact('vente', 'ventes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vente_id' => 'required|exists:ventes,id',
            'motif' => 'required|string|min:10',
            'type' => 'required|in:total,partiel',
            'items' => 'required_if:type,partiel|array',
            'items.*.detail_vente_id' => 'required|exists:detail_ventes,id',
            'items.*.quantite' => 'required|integer|min:1',
        ]);

        try {
            $retour = DB::transaction(function () use ($data) {
                $vente = Vente::with('details.product')->findOrFail($data['vente_id']);

                if ($vente->isAnnulee()) {
                    throw new \RuntimeException('Cette facture est annulée.');
                }

                $numero = 'RET-'.now()->format('Ymd').'-'.str_pad((Retour::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);
                $montant = 0;
                $itemsToProcess = [];

                if ($data['type'] === 'total') {
                    foreach ($vente->details as $detail) {
                        $qte = $detail->quantiteRestante();
                        if ($qte > 0) {
                            $itemsToProcess[] = ['detail' => $detail, 'quantite' => $qte];
                        }
                    }
                } else {
                    foreach ($data['items'] as $item) {
                        $detail = DetailVente::findOrFail($item['detail_vente_id']);
                        if ($item['quantite'] > $detail->quantiteRestante()) {
                            throw new \RuntimeException("Quantité retour invalide pour {$detail->product->name}");
                        }
                        $itemsToProcess[] = ['detail' => $detail, 'quantite' => $item['quantite']];
                    }
                }

                if (empty($itemsToProcess)) {
                    throw new \RuntimeException('Aucun article à retourner.');
                }

                $retour = Retour::create([
                    'numero_retour' => $numero,
                    'vente_id' => $vente->id,
                    'user_id' => auth()->id(),
                    'type' => $data['type'],
                    'motif' => $data['motif'],
                ]);

                foreach ($itemsToProcess as $item) {
                    $detail = $item['detail'];
                    $qte = $item['quantite'];
                    $totalLigne = $detail->prix_unitaire * $qte;
                    $montant += $totalLigne;

                    RetourDetail::create([
                        'retour_id' => $retour->id,
                        'product_id' => $detail->product_id,
                        'detail_vente_id' => $detail->id,
                        'quantite' => $qte,
                        'prix_unitaire' => $detail->prix_unitaire,
                        'total_ligne' => $totalLigne,
                    ]);

                    $detail->increment('quantite_retournee', $qte);
                    StockService::adjust($detail->product, $qte, 'retour', $numero, $data['motif']);
                }

                $retour->update(['montant_rembourse' => $montant]);

                $allReturned = $vente->details()->get()->every(fn ($d) => $d->quantiteRestante() === 0);
                $vente->update(['statut' => $allReturned ? 'retournee' : 'partiellement_retournee']);

                ActivityLogger::log('retour', 'retours', "Retour {$numero} - {$montant} FCFA", ['retour_id' => $retour->id]);

                return $retour;
            });

            return redirect()->route('retours.index')->with('success', '↩️ Retour enregistré avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', '❌ '.$e->getMessage());
        }
    }

    public function show(Retour $retour)
    {
        $retour->load(['vente', 'user', 'details.product']);

        return view('retours.show', compact('retour'));
    }
}
