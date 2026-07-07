<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $user = \App\Models\User::first();
    if (!$user) {
        throw new \Exception("No user found to test.");
    }

    echo "Testing Chatbot Service for User: {$user->name} (ID: {$user->id})\n";

    // 1. Resolve ChatService
    $chatService = app(\App\Services\Contracts\ChatServiceInterface::class);

    // 2. Fetch history (should be empty initially, or contain messages if run multiple times)
    $historyBefore = $chatService->getChatHistory($user);
    echo "  Initial history messages count: " . $historyBefore->count() . "\n";

    // 3. Send message 1
    $question1 = "Who is the top performer?";
    echo "  Sending message: '{$question1}'\n";
    $reply1 = $chatService->sendMessage($user, $question1);
    echo "  AI Response 1: '{$reply1}'\n\n";

    // 4. Send message 2
    $question2 = "How many tasks are completed?";
    echo "  Sending message: '{$question2}'\n";
    $reply2 = $chatService->sendMessage($user, $question2);
    echo "  AI Response 2: '{$reply2}'\n\n";

    // 5. Fetch history after
    $historyAfter = $chatService->getChatHistory($user);
    echo "  Final history messages count: " . $historyAfter->count() . "\n";
    foreach ($historyAfter as $idx => $msg) {
        echo "    Message " . ($idx + 1) . " [{$msg->role}]: {$msg->message}\n";
    }

} catch (\Exception $e) {
    echo "Verification Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}



