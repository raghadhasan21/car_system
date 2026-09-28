<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    // بدء محادثة أو جلبها
    public function startOrGet(Request $request, $userId)
    {
        $buyerId = $request->user()->id;
        $sellerId = $userId;

        if ($buyerId == $sellerId) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك محادثة نفسك'], 400);
        }

        $conversation = Conversation::firstOrCreate([
            'buyer_id' => min($buyerId, $sellerId),
            'seller_id' => max($buyerId, $sellerId),
        ]);

        $conversation->load('buyer', 'seller', 'messages.sender');
        $this->fixAvatar($conversation);

        return response()->json(['success' => true, 'data' => $conversation]);
    }

    // محادثاتي
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = Conversation::where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->with('buyer', 'seller', 'messages.sender')
            ->latest()
            ->get();

        foreach ($conversations as $conversation) {
            $this->fixAvatar($conversation);
        }

        return response()->json(['success' => true, 'data' => $conversations]);
    }

    // تفاصيل محادثة
    public function show($id)
    {
        $conversation = Conversation::with('buyer', 'seller', 'messages.sender')->find($id);

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'المحادثة غير موجودة'], 404);
        }

        $this->fixAvatar($conversation);

        return response()->json(['success' => true, 'data' => $conversation]);
    }

    // إرسال رسالة
    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate(['message' => 'required|string']);

        $conversation = Conversation::find($conversationId);
        if (!$conversation) return response()->json(['success' => false], 404);

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $request->user()->id,
            'message' => $request->message,
        ]);

        // ✅ تجهيز بيانات المرسل
        $message->load('sender');
        if ($message->sender && $message->sender->avatar) {
            $message->sender->avatar = $this->fullUrl($message->sender->avatar);
        }

        $receiverId = ($message->sender_id == $conversation->buyer_id)
            ? $conversation->seller_id
            : $conversation->buyer_id;

        $receiver = User::find($receiverId);
        if ($receiver) {
            $receiver->notify(new NewMessage($message));
        }

        return response()->json(['success' => true, 'data' => $message], 201);
    }

    private function fixAvatar($conversation)
    {
        if ($conversation->buyer && $conversation->buyer->avatar) {
            $conversation->buyer->avatar = $this->fullUrl($conversation->buyer->avatar);
        }

        if ($conversation->seller && $conversation->seller->avatar) {
            $conversation->seller->avatar = $this->fullUrl($conversation->seller->avatar);
        }

        foreach ($conversation->messages as $message) {
            if ($message->sender && $message->sender->avatar) {
                $message->sender->avatar = $this->fullUrl($message->sender->avatar);
            }
        }
    }

    private function fullUrl($path)
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return asset('storage/' . $path);
    }
}