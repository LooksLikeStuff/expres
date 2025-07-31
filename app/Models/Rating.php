<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'rated_user_id',
        'rater_user_id',
        'score',
        'comment',
        'role',
    ];

    /**
     * Получить сделку, к которой относится рейтинг
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    /**
     * Получить пользователя, которого оценивали
     */
    public function ratedUser()
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }

    /**
     * Получить пользователя, который оставил оценку
     */
    public function raterUser()
    {
        return $this->belongsTo(User::class, 'rater_user_id');
    }
}
