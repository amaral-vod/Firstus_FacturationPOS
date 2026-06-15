<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use Illuminate\Http\Request;

class LoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        $histories = LoginHistory::with('user')
            ->when($request->success !== null, fn ($q) => $q->where('success', $request->success))
            ->when($request->date, fn ($q) => $q->whereDate('created_at', $request->date))
            ->latest()
            ->paginate(30);

        return view('admin.login-history.index', compact('histories'));
    }
}
