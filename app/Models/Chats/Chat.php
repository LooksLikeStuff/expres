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

    // В модели Chat.php

    public function userChats()
    {
        return $this->hasMany(UserChat::class, 'chat_id', 'id');
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
            $opportunity = $this->getOpportunityFor(auth()->id());

            return !is_null($opportunity->getRawOriginal('avatar_url')) //Если аватарка есть
                ? $opportunity->profile_avatar //Получаем полный путь до нее
                : $this->getPrivateBaseImage(); //Иначе заглушку
        }

        return $this->avatar ? asset('storage/' . $this->avatar) : $this->getGroupBaseImage();
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
