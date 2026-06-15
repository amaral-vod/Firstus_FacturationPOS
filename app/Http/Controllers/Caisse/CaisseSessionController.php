<?php

namespace App\Http\Controllers\Caisse;

use App\Http\Controllers\Controller;
use App\Models\CaisseSession;
use App\Models\Vente;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaisseSessionController extends Controller
{
    public function index()
    {
        $session = CaisseSession::sessionOuverte(Auth::id());
        $sessions = CaisseSession::with('user')->latest()->paginate(15);

        return view('caisse.sessions.index', compact('session', 'sessions'));
    }

    public function ouvrir(Request $request)
    {
        if (CaisseSession::sessionOuverte(Auth::id())) {
            return back()->with('error', '❌ Une caisse est déjà ouverte.');
        }

        $data = $request->validate(['fond_initial' => 'required|numeric|min:0']);

        CaisseSession::create([
            'user_id' => Auth::id(),
            'opened_at' => now(),
            'fond_initial' => $data['fond_initial'],
            'statut' => 'ouverte',
        ]);

        ActivityLogger::log('ouverture_caisse', 'caisse', 'Ouverture caisse — fond: '.$data['fond_initial']);

        return back()->with('success', '✅ Caisse ouverte.');
    }

    public function fermer(Request $request)
    {
        $session = CaisseSession::sessionOuverte(Auth::id());
        if (! $session) {
            return back()->with('error', '❌ Aucune caisse ouverte.');
        }

        $data = $request->validate(['fond_reel' => 'required|numeric|min:0', 'notes' => 'nullable|string']);

        $ventes = Vente::where('caisse_session_id', $session->id)->where('statut', 'complete')->sum('total');
        $fondTheorique = $session->fond_initial + $ventes;
        $ecart = $data['fond_reel'] - $fondTheorique;

        $session->update([
            'closed_at' => now(),
            'fond_theorique' => $fondTheorique,
            'fond_reel' => $data['fond_reel'],
            'ecart' => abs($ecart),
            'ecart_type' => $ecart > 0 ? 'surplus' : ($ecart < 0 ? 'manquant' : 'aucun'),
            'statut' => 'fermee',
            'notes' => $data['notes'] ?? null,
        ]);

        ActivityLogger::log('fermeture_caisse', 'caisse', "Fermeture caisse — écart: {$ecart}");

        return back()->with('success', '✅ Caisse fermée.');
    }
}
