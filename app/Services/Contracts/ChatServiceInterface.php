<?php

namespace App\Services\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface ChatServiceInterface
{
    /**
     * Send a message to the AI, saving it to database and getting the response.
     *
     * @param User $user
     * @param string $message
     * @return string
     */
    public function sendMessage(User $user, string $message): string;

    /**
     * Get the recent chat history for the user.
     *
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getChatHistory(User $user, int $limit = 50): Collection;
}
