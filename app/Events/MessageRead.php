<?php

namespace App\Events;

use App\Models\Chats\ReadReceipt;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private ReadReceipt $readReceipt;
    /**
     * Create a new event instance.
     */
    public function __construct(ReadReceipt $readReceipt)
    {
        $this->readReceipt = $readReceipt;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->readReceipt->message->chat_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->readReceipt->message_id,
            'user_ids' => $this->readReceipt->user_id,
            'read_at' => $this->readReceipt->read_at,
        ];
    }
}
