<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('resident.notifications.index', [
            'notifications' => $user->notifications()->latest()->paginate(10),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()
            ->route('resident.notifications.index')
            ->with('success', 'All notifications marked as read.');
    }
}
