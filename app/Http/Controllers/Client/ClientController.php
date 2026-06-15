<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::with('site')->latest()->paginate(20);
        $totalDettes = Client::sum('balance_due');

        return view('clients.index', compact('clients', 'totalDettes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);
        $data['code'] = 'CLI-'.strtoupper(Str::random(6));

        Client::create($data);

        return back()->with('success', '✅ Client ajouté.');
    }

    public function credits()
    {
        $credits = ClientCredit::with('client')->latest()->paginate(20);
        $enRetard = ClientCredit::where('date_echeance', '<', today())->where('statut', '!=', 'paye')->count();

        return view('clients.credits', compact('credits', 'enRetard'));
    }
}
