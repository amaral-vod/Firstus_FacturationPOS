<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where(function ($q) {
            $q->where('user_id', Auth::id())->orWhereNull('user_id');
        })
            ->latest()
            ->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Notification $notification)
    {
        $notification->update(['read_at' => now()]);

        return back();
    }

    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', '✅ Notifications lues.');
    }
}
