<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealChangeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'user_id',
        'user_name',
        'action_type',
        'changes',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * Связь с моделью Deal
     * Используем leftJoin для получения информации о сделке, даже если она удалена
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    /**
     * Связь с моделью User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить тип действия с переводом
     */
    public function getActionTypeTranslatedAttribute()
    {
        $translations = [
            'create' => 'Создание',
            'update' => 'Редактирование',
            'delete' => 'Удаление',
            'restore' => 'Восстановление',
            'status_change' => 'Изменение статуса',
        ];

        return $translations[$this->action_type] ?? $this->action_type;
    }

    /**
     * Скоп для фильтрации по типу действия
     */
    public function scopeByActionType($query, $actionType)
    {
        if ($actionType) {
            return $query->where('action_type', $actionType);
        }
        return $query;
    }

    /**
     * Скоп для фильтрации по пользователю
     */
    public function scopeByUser($query, $userId)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }
        return $query;
    }

    /**
     * Скоп для фильтрации по дате
     */
    public function scopeByDateRange($query, $dateFrom, $dateTo)
    {
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        return $query;
    }

    /**
     * Скоп для поиска по тексту
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('user_name', 'LIKE', "%{$search}%")
                  ->orWhere('deal_id', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('deal', function($dealQuery) use ($search) {
                      $dealQuery->where('client_phone', 'LIKE', "%{$search}%")
                               ->orWhere('client_email', 'LIKE', "%{$search}%")
                               ->orWhere('project_name', 'LIKE', "%{$search}%")
                               ->orWhere('client_name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereJsonContains('changes', $search);
            });
        }
        return $query;
    }
}
