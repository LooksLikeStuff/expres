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

    public function unreadCountForUser(int $userId): int
    {
        return $this->messages()
            ->whereDoesntHave('readReceipts', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('sender_id', '!=', $userId)
            ->count();
    }
}
