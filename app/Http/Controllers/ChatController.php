<?php

namespace App\Http\Controllers;

use App\Services\Contracts\ChatServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected ChatServiceInterface $chatService;

    public function __construct(ChatServiceInterface $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Send message and get AI response.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $user = Auth::user() ?? \App\Models\User::first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $userMessage = $request->input('message');
        
        try {
            $reply = $this->chatService->sendMessage($user, $userMessage);
            return response()->json([
                'success' => true,
                'response' => $reply,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch chat history.
     *
     * @return JsonResponse
     */
    public function getHistory(): JsonResponse
    {
        $user = Auth::user() ?? \App\Models\User::first();
        if (!$user) {
            return response()->json(['history' => []]);
        }

        $history = $this->chatService->getChatHistory($user)
            ->map(function ($msg) {
                return [
                    'role' => $msg->role,
                    'message' => $msg->message,
                    'created_at' => $msg->created_at ? $msg->created_at->toIso8601String() : now()->toIso8601String(),
                ];
            });

        return response()->json(['history' => $history]);
    }
}
