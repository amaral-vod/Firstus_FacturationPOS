<?php

namespace App\Http\Controllers\Facturation;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Facture;
use App\Models\FactureDetail;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Vente;
use App\Services\ProformaFormatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FactureController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        $factures = Facture::with(['client', 'user', 'vente'])
            ->when($type !== 'all', fn ($q) => $q->where('type', $type))
            ->latest()
            ->paginate(20);

        $counts = [
            'proforma' => Facture::where('type', 'proforma')->count(),
            'facture' => Facture::where('type', 'facture')->count(),
            'devis' => Facture::where('type', 'devis')->count(),
            'bon_commande' => Facture::where('type', 'bon_commande')->count(),
            'bon_livraison' => Facture::where('type', 'bon_livraison')->count(),
            'ticket' => Facture::where('type', 'ticket')->count(),
            'facture_a4' => Facture::where('type', 'facture_a4')->count(),
        ];

        return view('facturation.index', compact('factures', 'counts', 'type'));
    }

    public function create(Request $request)
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('stock')->where('is_active', true)->orderBy('name')->get();
        $vente = $request->filled('vente_id') ? Vente::with('details.product')->find($request->vente_id) : null;

        return view('facturation.create-proforma', compact('clients', 'products', 'vente'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'client_nom' => 'nullable|string|max:255',
            'client_adresse' => 'nullable|string|max:500',
            'client_telephone' => 'nullable|string|max:30',
            'format_papier' => 'required|in:auto,a4,a5,ticket',
            'date_echeance' => 'nullable|date',
            'notes' => 'nullable|string',
            'remise' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.designation' => 'required|string|max:255',
            'items.*.quantite' => 'required|integer|min:1',
            'items.*.prix_unitaire' => 'required|numeric|min:0',
            'items.*.product_id' => 'nullable|exists:products,id',
        ]);

        $facture = DB::transaction(function () use ($data) {
            $sousTotal = 0;
            foreach ($data['items'] as $item) {
                $sousTotal += $item['quantite'] * $item['prix_unitaire'];
            }

            $remise = $data['remise'] ?? 0;
            $tvaPct = (float) Setting::get('tva', 0);
            $base = max(0, $sousTotal - $remise);
            $tva = $tvaPct > 0 ? round($base * $tvaPct / 100, 2) : 0;
            $total = $base + $tva;

            $numero = 'PRO-'.now()->format('Ymd').'-'.str_pad(
                Facture::where('type', 'proforma')->whereDate('created_at', today())->count() + 1,
                4, '0', STR_PAD_LEFT
            );

            $facture = Facture::create([
                'numero' => $numero,
                'type' => 'proforma',
                'format_papier' => $data['format_papier'],
                'client_id' => $data['client_id'] ?? null,
                'client_nom' => $data['client_nom'] ?? null,
                'client_adresse' => $data['client_adresse'] ?? null,
                'client_telephone' => $data['client_telephone'] ?? null,
                'user_id' => Auth::id(),
                'sous_total' => $sousTotal,
                'remise' => $remise,
                'tva' => $tva,
                'total' => $total,
                'statut' => 'valide',
                'date_echeance' => $data['date_echeance'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $i => $item) {
                FactureDetail::create([
                    'facture_id' => $facture->id,
                    'product_id' => $item['product_id'] ?? null,
                    'designation' => $item['designation'],
                    'quantite' => $item['quantite'],
                    'prix_unitaire' => $item['prix_unitaire'],
                    'total_ligne' => $item['quantite'] * $item['prix_unitaire'],
                    'ordre' => $i,
                ]);
            }

            return $facture;
        });

        return redirect()->route('facturation.imprimer', $facture)
            ->with('success', '✅ Facture proforma créée.');
    }

    public function show(Facture $facture)
    {
        $facture->load(['client', 'user', 'vente.details.product', 'details.product']);
        $format = ProformaFormatService::resolve($facture);

        return view('facturation.show', compact('facture', 'format'));
    }

    public function imprimer(Facture $facture, Request $request)
    {
        $facture->load(['client', 'user', 'details.product', 'vente.details.product']);
        $facture->update(['imprime_le' => now()]);

        $format = $request->get('format', ProformaFormatService::resolve($facture));

        $settings = [
            'nom_magasin' => Setting::get('nom_magasin', 'Firstus POS'),
            'adresse' => Setting::get('adresse', ''),
            'telephone' => Setting::get('telephone', ''),
            'email' => Setting::get('email', ''),
            'ifu' => Setting::get('ifu', ''),
            'rccm' => Setting::get('rccm', ''),
            'devise' => Setting::get('devise', 'FCFA'),
            'tva' => Setting::get('tva', '0'),
        ];

        $lignes = ProformaFormatService::lignes($facture);
        $totalArticles = $lignes->sum('quantite');

        return view('facturation.print-proforma', compact('facture', 'format', 'settings', 'lignes', 'totalArticles'));
    }

    public function reimprimer(Facture $facture)
    {
        if (in_array($facture->type, ['proforma', 'devis', 'facture', 'facture_a4'])) {
            return redirect()->route('facturation.imprimer', $facture);
        }

        $facture->update(['imprime_le' => now()]);

        if ($facture->vente_id) {
            return redirect()->route('caisse.ticket', $facture->vente_id);
        }

        return back()->with('success', '🖨️ Réimpression enregistrée.');
    }
}
