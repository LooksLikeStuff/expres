<?php

namespace App\Models\Chats;

use App\Scopes\OnlyActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_id',
        'joined_at',
        'left_at',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new OnlyActiveScope);
    }
}
