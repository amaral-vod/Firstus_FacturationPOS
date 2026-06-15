<?php

namespace App\Http\Controllers\Journal;

use App\Http\Controllers\Controller;
use App\Models\JournalActivite;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $activites = JournalActivite::with('user')
            ->when($request->module, fn ($q) => $q->where('module', $request->module))
            ->when($request->action, fn ($q) => $q->where('action', $request->action))
            ->when($request->date, fn ($q) => $q->whereDate('created_at', $request->date))
            ->latest()
            ->paginate(30);

        $modules = JournalActivite::distinct()->pluck('module');
        $actions = JournalActivite::distinct()->pluck('action');

        return view('journal.index', compact('activites', 'modules', 'actions'));
    }
}
