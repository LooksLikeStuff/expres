<?php

namespace App\Models\Chats;

use App\Enums\MessageType;
use App\Models\ChatGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id',
        'chat_id',
        'reply_to_id',
        'content',
        'type',
    ];

    protected $casts = [
        'type' => MessageType::class,
    ];

    protected $appends = ['time', 'sender_name', 'read_at'];

    public function getTimeAttribute(): string
    {
        return $this->created_at?->toTimeString('minute');
    }

    public function getSenderNameAttribute(): string
    {
        return $this->sender()->first(['name'])->name;
    }

    public function getReadAtAttribute()
    {
        return $this->readReceipt?->read_at;
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    // Message.php
    public function readReceipt()
    {
        return $this->hasOne(ReadReceipt::class)
            ->where('user_id', auth()->id());
    }


    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
    /**
     * Получить отправителя сообщения.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Проверить, прочитано ли сообщение.
     *
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Отметить сообщение как прочитанное.
     *
     * @return void
     */
    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }
}
