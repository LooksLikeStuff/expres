<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'name',
        'phone',
        'email',
        'city',
        'timezone',
        'info',
        'account_link',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Связь с сделкой
     */
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    /**
     * Получить отформатированный номер телефона
     */
    public function getFormattedPhoneAttribute(): string
    {
        return $this->formatPhone($this->phone);
    }

    /**
     * Получить короткое имя (первое слово)
     */
    public function getShortNameAttribute(): string
    {
        return explode(' ', trim($this->name))[0];
    }

    /**
     * Проверить, есть ли полная контактная информация
     */
    public function hasFullContactInfo(): bool
    {
        return !empty($this->name) && !empty($this->phone) && !empty($this->email);
    }

    /**
     * Получить информацию о местоположении
     */
    public function getLocationInfoAttribute(): ?string
    {
        $location = [];
        
        if ($this->city) {
            $location[] = $this->city;
        }
        
        if ($this->timezone) {
            $location[] = $this->timezone;
        }
        
        return !empty($location) ? implode(', ', $location) : null;
    }

    /**
     * Форматирование номера телефона
     */
    private function formatPhone(string $phone): string
    {
        // Убираем все символы кроме цифр
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Если номер начинается с 8, заменяем на +7
        if (str_starts_with($cleaned, '8') && strlen($cleaned) === 11) {
            $cleaned = '7' . substr($cleaned, 1);
        }
        
        // Если номер начинается с 7 и содержит 11 цифр
        if (str_starts_with($cleaned, '7') && strlen($cleaned) === 11) {
            return '+7 (' . substr($cleaned, 1, 3) . ') ' . 
                   substr($cleaned, 4, 3) . '-' . 
                   substr($cleaned, 7, 2) . '-' . 
                   substr($cleaned, 9, 2);
        }
        
        // Возвращаем исходный номер, если не удалось отформатировать
        return $phone;
    }

    /**
     * Поиск по телефону
     */
    public static function findByPhone(string $phone): ?self
    {
        // Нормализуем номер телефона для поиска
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);
        
        return static::where('phone', 'like', '%' . $normalizedPhone . '%')->first();
    }

    /**
     * Поиск по email
     */
    public static function findByEmail(string $email): ?self
    {
        return static::where('email', $email)->first();
    }
}