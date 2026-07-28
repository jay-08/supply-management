<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\ActivityLog;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderByDesc('created_at')->paginate(20);

        if ($request->has('ajax')) {
            return view('notifications.dropdown', compact('notifications'));
        }

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(int $id)
    {
        Notification::where('id', $id)->where('user_id', auth()->id())->update(['is_read' => true]);
        return back();
    }

    public function markAllRead()
    {
        Notification::where('user_id', auth()->id())->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function count()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotificationsCount()
        ]);
    }
}
