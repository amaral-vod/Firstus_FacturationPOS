<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\InventorySession;
use App\Models\Site;
use App\Services\ActivityLogger;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventorySessionController extends Controller
{
    public function index()
    {
        $sessions = InventorySession::with(['site', 'user', 'validator'])
            ->latest('inventory_date')
            ->latest('id')
            ->paginate(15);

        return view('stock.inventories.index', compact('sessions'));
    }

    public function create()
    {
        $sites = Site::where('is_active', true)->orderBy('name')->get();
        $defaultSiteId = InventoryService::defaultSiteId();

        return view('stock.inventories.create', compact('sites', 'defaultSiteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'inventory_date' => 'required|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $session = InventoryService::createSession(
            (int) $data['site_id'],
            $data['inventory_date'],
            $data['notes'] ?? null
        );

        ActivityLogger::log('inventaire_cree', 'stock', "Inventaire {$session->reference} créé");

        return redirect()
            ->route('stock.inventories.show', $session)
            ->with('success', "✅ Inventaire {$session->reference} créé. Saisissez les quantités comptées.");
    }

    public function show(InventorySession $inventory)
    {
        $inventory->load(['lines.product.category', 'site', 'user', 'validator']);

        return view('stock.inventories.show', [
            'session' => $inventory,
            'reasons' => InventorySession::REASONS,
        ]);
    }

    public function update(Request $request, InventorySession $inventory)
    {
        if (! $inventory->isEditable()) {
            return back()->with('error', '❌ Inventaire non modifiable.');
        }

        $lines = $request->input('lines', []);

        try {
            InventoryService::updateLines($inventory, $lines);

            if ($request->input('action') === 'validate') {
                if (! Auth::user()->hasPermission('stock.inventory.validate')) {
                    return back()->with('error', '❌ Validation réservée à un responsable.');
                }

                InventoryService::validateSession($inventory->fresh(), Auth::id());
                ActivityLogger::log('inventaire_valide', 'stock', "Inventaire {$inventory->reference} validé");

                return redirect()
                    ->route('stock.inventories.report', $inventory)
                    ->with('success', '✅ Inventaire validé — stock mis à jour.');
            }

            ActivityLogger::log('inventaire_maj', 'stock', "Inventaire {$inventory->reference} mis à jour");

            return back()->with('success', '💾 Brouillon enregistré.');
        } catch (\Throwable $e) {
            return back()->with('error', '❌ '.$e->getMessage())->withInput();
        }
    }

    public function cancel(InventorySession $inventory)
    {
        try {
            InventoryService::cancelSession($inventory);
            ActivityLogger::log('inventaire_annule', 'stock', "Inventaire {$inventory->reference} annulé");

            return redirect()
                ->route('stock.inventories.index')
                ->with('success', '🚫 Inventaire annulé.');
        } catch (\Throwable $e) {
            return back()->with('error', '❌ '.$e->getMessage());
        }
    }

    public function report(InventorySession $inventory)
    {
        $inventory->load(['lines.product.category', 'site', 'user', 'validator']);

        return view('stock.inventories.report', [
            'session' => $inventory,
            'reasons' => InventorySession::REASONS,
        ]);
    }
}
