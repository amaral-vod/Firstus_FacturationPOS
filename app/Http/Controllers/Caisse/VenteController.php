<?php

namespace App\Http\Controllers\Caisse;

use App\Http\Controllers\Controller;
use App\Models\DetailVente;
use App\Models\Facture;
use App\Models\CaisseSession;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Vente;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VenteController extends Controller
{
    public function index()
    {
        $products = Product::with('stock', 'category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = $products->pluck('category')->filter()->unique('id');

        return view('caisse.index', compact('products', 'categories'));
    }

    public function historique(Request $request)
    {
        $ventes = Vente::with('user')
            ->when($request->date, fn ($q) => $q->whereDate('created_at', $request->date))
            ->latest()
            ->paginate(20);

        return view('caisse.historique', compact('ventes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantite' => 'required|integer|min:1',
            'remise' => 'nullable|numeric|min:0',
            'montant_paye' => 'required|numeric|min:0',
            'mode_paiement' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $vente = DB::transaction(function () use ($data) {
                $sousTotal = 0;
                $lines = [];

                foreach ($data['items'] as $item) {
                    $product = Product::with('stock')->findOrFail($item['product_id']);
                    $prix = $product->effective_price;
                    $totalLigne = $prix * $item['quantite'];
                    $sousTotal += $totalLigne;

                    if (($product->stock?->quantity ?? 0) < $item['quantite']) {
                        throw new \RuntimeException("Stock insuffisant pour {$product->name}");
                    }

                    $lines[] = [
                        'product' => $product,
                        'quantite' => $item['quantite'],
                        'prix_unitaire' => $prix,
                        'total_ligne' => $totalLigne,
                    ];
                }

                $remise = $data['remise'] ?? 0;
                $total = max(0, $sousTotal - $remise);
                $montantPaye = $data['montant_paye'];
                $monnaie = max(0, $montantPaye - $total);

                $numero = 'FAC-'.now()->format('Ymd').'-'.str_pad((Vente::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);
                $session = CaisseSession::sessionOuverte(auth()->id());

                $vente = Vente::create([
                    'numero_facture' => $numero,
                    'user_id' => auth()->id(),
                    'caisse_session_id' => $session?->id,
                    'sous_total' => $sousTotal,
                    'remise' => $remise,
                    'total' => $total,
                    'montant_paye' => $montantPaye,
                    'monnaie' => $monnaie,
                    'mode_paiement' => $data['mode_paiement'],
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($lines as $line) {
                    DetailVente::create([
                        'vente_id' => $vente->id,
                        'product_id' => $line['product']->id,
                        'quantite' => $line['quantite'],
                        'prix_unitaire' => $line['prix_unitaire'],
                        'total_ligne' => $line['total_ligne'],
                    ]);

                    StockService::adjust($line['product'], $line['quantite'], 'sortie', $numero, 'Vente');
                }

                Facture::create([
                    'numero' => $numero,
                    'type' => 'ticket',
                    'vente_id' => $vente->id,
                    'user_id' => auth()->id(),
                    'sous_total' => $sousTotal,
                    'remise' => $remise,
                    'total' => $total,
                    'statut' => 'paye',
                    'imprime_le' => now(),
                ]);

                Facture::create([
                    'numero' => $numero.'-FA4',
                    'type' => 'facture_a4',
                    'vente_id' => $vente->id,
                    'user_id' => auth()->id(),
                    'sous_total' => $sousTotal,
                    'remise' => $remise,
                    'total' => $total,
                    'statut' => 'valide',
                ]);

                ActivityLogger::log('vente', 'caisse', "Vente {$numero} - Total: {$total} FCFA", ['vente_id' => $vente->id]);

                return $vente->load('details.product', 'user');
            });

            return response()->json([
                'success' => true,
                'vente' => $vente,
                'ticket_url' => route('caisse.ticket', $vente),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function show(Vente $vente)
    {
        $vente->load('details.product', 'user', 'annulation', 'retours');

        return view('caisse.show', compact('vente'));
    }

    public function ticket(Vente $vente)
    {
        $vente->load('details.product', 'user');
        $settings = [
            'nom_magasin' => Setting::get('nom_magasin', 'Firstus POS'),
            'adresse' => Setting::get('adresse', ''),
            'telephone' => Setting::get('telephone', ''),
            'ticket_width' => Setting::get('ticket_width', '80'),
        ];

        return view('caisse.ticket', compact('vente', 'settings'));
    }
}
