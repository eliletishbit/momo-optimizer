<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Affiche la liste des notifications.
     */
    public function index(): View
    {
        $notifications = auth()->user()->notifications()->paginate(20);

        return view('pages.notifications', [
            'notifications' => $notifications
        ]);
    }

    /**
     * Marque une notification comme lue.
     */
    public function markAsRead($id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', 'Notification marquée comme lue.');
    }
}
