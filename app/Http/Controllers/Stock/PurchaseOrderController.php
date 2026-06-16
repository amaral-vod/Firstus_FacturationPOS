<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Services\ActivityLogger;
use App\Services\PurchaseOrderService;
use App\Services\StockService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $siteId = StockService::resolveSiteId($request->integer('site_id') ?: null);
        $sites = StockService::activeSites();

        $orders = PurchaseOrder::with(['fournisseur', 'site', 'user'])
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('stock.commandes.index', compact('orders', 'sites', 'siteId'));
    }

    public function show(PurchaseOrder $commande)
    {
        $commande->load(['fournisseur', 'site', 'user', 'lines.product']);

        return view('stock.commandes.show', ['order' => $commande]);
    }

    public function generate(Request $request)
    {
        $siteId = StockService::resolveSiteId($request->integer('site_id') ?: null);
        $result = PurchaseOrderService::generateFromLowStock($siteId);

        ActivityLogger::log(
            'commande_fournisseur',
            'stock',
            count($result['orders']).' commande(s) générée(s) depuis stock faible'
        );

        $message = '✅ '.count($result['orders']).' bon(s) de commande créé(s).';
        if (! empty($result['skipped'])) {
            $message .= ' '.count($result['skipped']).' produit(s) sans fournisseur ignoré(s).';
        }

        return redirect()
            ->route('stock.commandes.index', ['site_id' => $siteId])
            ->with('success', $message)
            ->with('commande_skipped', $result['skipped']);
    }

    public function updateStatus(Request $request, PurchaseOrder $commande)
    {
        $data = $request->validate([
            'status' => 'required|in:brouillon,envoyee,annulee',
        ]);

        if (! in_array($commande->status, ['brouillon', 'envoyee'], true) && $data['status'] !== 'annulee') {
            return back()->with('error', '❌ Statut non modifiable.');
        }

        $commande->update(['status' => $data['status']]);

        return back()->with('success', '✅ Statut mis à jour.');
    }

    public function receive(Request $request, PurchaseOrder $commande)
    {
        $data = $request->validate([
            'received' => 'required|array',
            'received.*' => 'nullable|integer|min:0',
        ]);

        try {
            PurchaseOrderService::receive($commande, $data['received']);
            ActivityLogger::log('reception_commande', 'stock', "Réception {$commande->reference}");

            return back()->with('success', '✅ Réception enregistrée et stock mis à jour.');
        } catch (\Throwable $e) {
            return back()->with('error', '❌ '.$e->getMessage());
        }
    }
}
