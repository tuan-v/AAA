<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $service) {}

    public function index(Request $request)
    {
        $notifications = $this->service->getUserNotifications(
            auth()->id(),
            $request->boolean('unread_only'),
            min(max($request->integer('per_page', 20), 1), 100)
        );

        return response()->json(['success' => true, 'data' => $notifications]);
    }

    public function unreadCount()
    {
        return response()->json([
            'success' => true,
            'count' => $this->service->getUnreadCount(auth()->id()),
            'by_category' => $this->service->getUnreadCountByCategory(auth()->id()),
        ]);
    }

    public function markAsRead(int $notification)
    {
        abort_unless($this->service->markAsRead($notification), 404);
        return response()->json(['message' => 'Đã đánh dấu thông báo là đã đọc.']);
    }

    public function markAllAsRead(Request $request)
    {
        $count = $this->service->markAllAsRead(auth()->id(), $request->input('category'));
        return response()->json(['message' => 'Đã đọc tất cả thông báo.', 'count' => $count]);
    }

    public function destroy(int $notification)
    {
        abort_unless($this->service->delete($notification), 404);
        return response()->json(['message' => 'Đã xóa thông báo.']);
    }
}
