<?php

namespace App\Events;

use App\Models\Chats\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Chat $chat;
    private int $userId;
    /**
     * Create a new event instance.
     */
    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return collect($this->chat->users)
            ->map(fn($user) => new PrivateChannel("user.{$user->id}"))
            ->toArray();
    }
    public function broadcastWith(): array
    {
        return [
            'chat_id' => $this->chat->id,
        ];
    }
}
