<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تعليم الإشعار كمقروء'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'تم تعليم جميع الإشعارات كمقروءة'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function destroy(Request $request, $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'الإشعار غير موجود'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإشعار'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function destroyAll(Request $request)
    {
        $request->user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف جميع الإشعارات'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}