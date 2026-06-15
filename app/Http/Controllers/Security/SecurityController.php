<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecurityController extends Controller
{
    public function index()
    {
        $logs = SecurityLog::with('user')->latest()->paginate(30);
        $sessions = \DB::table('sessions')->orderByDesc('last_activity')->limit(20)->get();

        return view('security.index', compact('logs', 'sessions'));
    }
}
