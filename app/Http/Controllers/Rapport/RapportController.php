<?php

namespace App\Http\Controllers\Rapport;

use App\Http\Controllers\Controller;
use App\Models\Annulation;
use App\Models\Retour;
use App\Models\Stock;
use App\Models\Vente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->get('periode', 'mois');
        $query = Vente::where('statut', 'complete');

        match ($periode) {
            'jour' => $query->whereDate('created_at', today()),
            'annee' => $query->whereYear('created_at', now()->year),
            default => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
        };

        $ca = $query->sum('total');
        $nbVentes = $query->count();

        $topProducts = DB::table('detail_ventes')
            ->join('products', 'detail_ventes.product_id', '=', 'products.id')
            ->join('ventes', 'detail_ventes.vente_id', '=', 'ventes.id')
            ->where('ventes.statut', 'complete')
            ->when($periode === 'jour', fn ($q) => $q->whereDate('ventes.created_at', today()))
            ->when($periode === 'mois', fn ($q) => $q->whereMonth('ventes.created_at', now()->month))
            ->when($periode === 'annee', fn ($q) => $q->whereYear('ventes.created_at', now()->year))
            ->select('products.name', DB::raw('SUM(detail_ventes.quantite) as qty'), DB::raw('SUM(detail_ventes.total_ligne) as revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        $etatStock = Stock::with('product')->orderBy('quantity')->get();
        $retours = Retour::when($periode === 'jour', fn ($q) => $q->whereDate('created_at', today()))
            ->when($periode === 'mois', fn ($q) => $q->whereMonth('created_at', now()->month))
            ->when($periode === 'annee', fn ($q) => $q->whereYear('created_at', now()->year))
            ->with('user')
            ->latest()
            ->get();

        $annulations = Annulation::when($periode === 'jour', fn ($q) => $q->whereDate('created_at', today()))
            ->when($periode === 'mois', fn ($q) => $q->whereMonth('created_at', now()->month))
            ->when($periode === 'annee', fn ($q) => $q->whereYear('created_at', now()->year))
            ->with(['vente', 'user'])
            ->latest()
            ->get();

        $caParJour = Vente::where('statut', 'complete')
            ->whereMonth('created_at', now()->month)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('rapports.index', compact(
            'ca', 'nbVentes', 'topProducts', 'etatStock',
            'retours', 'annulations', 'caParJour', 'periode'
        ));
    }
}
