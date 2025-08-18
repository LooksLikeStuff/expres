<?php

namespace App\Models\Chats;

use App\Enums\MessageType;
use App\Models\ChatGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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

    protected $appends = ['time', 'sender_name', 'read_at', 'formatted_time', 'is_own'];

    public function getTimeAttribute(): string
    {
        return $this->created_at?->toTimeString('minute');
    }

    public function getIsOwnAttribute()
    {
        return $this->sender_id == auth()->id();
    }
    public function getSenderNameAttribute(): string
    {
        return $this->sender()->first(['name'])->name;
    }

    public function getReadAtAttribute()
    {
        return $this->readReceipt?->read_at;
    }

    public function getFormattedTimeAttribute(): string
    {
        if (!$this->created_at) return '';

        $created = $this->created_at->copy();
        $now = Carbon::now();

        if ($created->isToday()) {
            return $created->format('H:i');
        }

        if ($created->isYesterday()) {
            return 'вчера';
        }

        return $created->format('d.m.Y');
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

    public function readReceipts()
    {
        return $this->hasMany(ReadReceipt::class);
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

    public function getContentForFront(): string
    {
        if ($this->content) return $this->content;

        return 'Файлы: '. $this->getAttachmentsCount();
    }

    public function getAttachmentsCount(): int
    {
        return $this->attachments()->count();
    }
}
