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

        return $this->users->firstWhere('id', '!=', $userId)->name;
    }
}
