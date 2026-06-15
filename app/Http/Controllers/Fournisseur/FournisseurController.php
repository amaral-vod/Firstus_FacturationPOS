<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use App\Models\FournisseurReglement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FournisseurController extends Controller
{
    public function index()
    {
        $fournisseurs = Fournisseur::latest()->paginate(20);
        $totalDettes = Fournisseur::sum('balance');

        return view('fournisseurs.index', compact('fournisseurs', 'totalDettes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
        ]);
        $data['code'] = 'FOU-'.strtoupper(Str::random(6));
        Fournisseur::create($data);

        return back()->with('success', '✅ Fournisseur ajouté.');
    }

    public function reglements()
    {
        $reglements = FournisseurReglement::with(['fournisseur', 'user'])->latest()->paginate(20);

        return view('fournisseurs.reglements', compact('reglements'));
    }

    public function storeReglement(Request $request)
    {
        $data = $request->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'montant' => 'required|numeric|min:0',
            'date_echeance' => 'nullable|date',
            'mode_paiement' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $data['user_id'] = Auth::id();
        FournisseurReglement::create($data);

        $fournisseur = Fournisseur::find($data['fournisseur_id']);
        $fournisseur->decrement('balance', $data['montant']);

        return back()->with('success', '✅ Règlement enregistré.');
    }
}
