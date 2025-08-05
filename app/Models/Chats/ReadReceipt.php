<?php

namespace App\Models\Chats;

use App\Exceptions\Chats\MessageReadException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ReadReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'user_id',
        'read_at',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * @throws MessageReadException
     */
    public function markAsRead(Carbon $time): void
    {
        if ($this->isRead()) {
            throw new MessageReadException();
        }

        $this->read_at = $time;
        $this->save();
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }
}
