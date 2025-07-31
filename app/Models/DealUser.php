<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DealUser extends Pivot
{
    /**
     * Таблица, связанная с моделью.
     *
     * @var string
     */
    protected $table = 'deal_user';
    
    /**
     * Указывает, должна ли модель иметь временные метки.
     *
     * @var bool
     */
    public $timestamps = true;
    
    /**
     * Атрибуты, которые можно массово присваивать.
     *
     * @var array
     */
    protected $fillable = [
        'deal_id',
        'user_id',
        'role',
    ];

    /**
     * Получить сделку, к которой относится связь.
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    /**
     * Получить пользователя, к которому относится связь.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Проверяет, может ли пользователь оценить сделку
     * 
     * @param int $userId Идентификатор пользователя
     * @param int $dealId Идентификатор сделки
     * @return bool
     */
    public static function userCanRateDeal($userId, $dealId)
    {
        // Проверяем связь и роль пользователя в сделке
        $dealUser = self::where('user_id', $userId)
                        ->where('deal_id', $dealId)
                        ->first();
        
        if (!$dealUser) {
            return false;
        }
        
        // Клиенты, координаторы и партнеры могут оценивать сделки
        return in_array($dealUser->role, ['user', 'coordinator', 'partner']);
    }
}
