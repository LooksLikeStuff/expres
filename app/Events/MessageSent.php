<?php

namespace App\Events;

use App\Models\Chats\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Message $message;
    private int $authUserId;
    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->chat_id),
        ];
    }

    public function broadcastWith(): array
    {
        $sender = $this->message->sender()->first(['name']);

        return [
            'id' => $this->message->id,
            'content' => $this->message->content,
            'attachments' => $this->message->attachments,
            'type' => $this->message->type->value,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $sender->name,
            'formatted_time' => $this->message->formatted_time,
        ];
    }

}
