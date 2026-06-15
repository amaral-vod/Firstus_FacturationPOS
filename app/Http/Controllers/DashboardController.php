<?php

namespace App\Http\Controllers;

use App\Models\Annulation;
use App\Models\Client;
use App\Models\ClientCredit;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\Product;
use App\Models\Retour;
use App\Models\Vente;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $ventesJour = Vente::where('statut', 'complete')->whereDate('created_at', today());
        $caJour = (clone $ventesJour)->sum('total');
        $caMois = Vente::where('statut', 'complete')->whereMonth('created_at', now()->month)->sum('total');

        $beneficeJour = DB::table('detail_ventes')
            ->join('products', 'detail_ventes.product_id', '=', 'products.id')
            ->join('ventes', 'detail_ventes.vente_id', '=', 'ventes.id')
            ->where('ventes.statut', 'complete')
            ->whereDate('ventes.created_at', today())
            ->selectRaw('SUM((detail_ventes.prix_unitaire - COALESCE(products.cost, 0)) * detail_ventes.quantite) as benefice')
            ->value('benefice') ?? 0;

        $stats = [
            'ca_jour' => $caJour,
            'ca_mois' => $caMois,
            'benefice_jour' => $beneficeJour,
            'ventes_jour' => $ventesJour->count(),
            'retours_mois' => Retour::whereMonth('created_at', now()->month)->count(),
            'annulations_mois' => Annulation::whereMonth('created_at', now()->month)->count(),
            'dettes_clients' => Client::sum('balance_due'),
            'dettes_fournisseurs' => Fournisseur::sum('balance'),
            'credits_en_retard' => ClientCredit::where('statut', 'en_retard')->count(),
            'factures_en_attente' => Facture::whereIn('statut', ['brouillon', 'valide'])->count(),
        ];

        $topProducts = DB::table('detail_ventes')
            ->join('products', 'detail_ventes.product_id', '=', 'products.id')
            ->join('ventes', 'detail_ventes.vente_id', '=', 'ventes.id')
            ->where('ventes.statut', 'complete')
            ->select('products.name', DB::raw('SUM(detail_ventes.quantite) as total_vendu'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_vendu')
            ->limit(5)
            ->get();

        $lowStock = StockService::lowStockProducts();

        return view('dashboard', compact('stats', 'topProducts', 'lowStock'));
    }
}
