<?php

namespace App\Models\Chats;

use App\Enums\ChatType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'avatar',
        'title',
        'deleted_at',
    ];

    protected $casts = [
        'type' => ChatType::class,
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_chats');
    }


    public function getTitleForUser(int $userId)
    {
        if ($this->type == ChatType::GROUP) {
            return $this->title;
        }

        return $this->getOpportunityFor($userId)?->name;
    }

    public function getOpportunityFor(int $userId): ?User
    {
        return $this->users->firstWhere('id', '!=', $userId);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function unreadMessages()
    {
        return $this->hasMany(Message::class)
            ->where('created_at', '>', function ($query) {
                $query->select('last_read_at')
                    ->from('chat_user')
                    ->whereColumn('chat_user.chat_id', 'messages.chat_id')
                    ->where('chat_user.user_id', auth()->id());
            });
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getAvatar()
    {
        if ($this->type == ChatType::PRIVATE) {
            return $this->getOpportunityFor(auth()->id())->getRawOriginal('avatar_url') ?? $this->getPrivateBaseImage();
        }

        return $this->avatar ?? $this->getGroupBaseImage();
    }

    public function getPrivateBaseImage()
    {
        return asset('img/chats/private/placeholder.png');
    }

    public function getGroupBaseImage()
    {
        return asset('img/chats/group/placeholder.png');
    }
}
