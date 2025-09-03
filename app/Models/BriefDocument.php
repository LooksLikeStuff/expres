<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class BriefDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'brief_id',
        'original_name',
        'filepath',
        'mime_type',
        'file_size',
    ];

    protected $appends = ['full_path', 'full_url'];
    public function getFullPathAttribute(): string
    {
        $expiresAt = Carbon::now()->addMinutes(60); // ссылка будет валидна 60 минут

        return Storage::disk('yandex')->temporaryUrl($this->filepath, $expiresAt);
    }
    /**
     * Связь с брифом
     */
    public function brief()
    {
        return $this->belongsTo(Brief::class);
    }

    /**
     * Получить полный URL файла
     */
    public function getFullUrlAttribute(): string
    {
        // Проверяем, что filepath существует и это строка
        if (!isset($this->filepath) || !is_string($this->filepath)) {
            return 'Файл недоступен';
        }

        // Если путь уже абсолютный URL
        if (str_starts_with($this->filepath, 'http://') || str_starts_with($this->filepath, 'https://')) {
            return $this->filepath;
        }

        // Иначе формируем URL от базового адреса приложения
        return config('app.url') . '/' . ltrim($this->filepath, '/');
    }

    /**
     * Проверка, является ли файл изображением
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Проверка, является ли файл PDF
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
