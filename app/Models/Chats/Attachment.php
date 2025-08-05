<?php

namespace App\Models\Chats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'path',
        'original_name',
        'mime_type',
        'filesize',
    ];

    protected $appends = ['full_path'];

    public function getFullPathAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Получить полный URL для вложения
     *
     * @return string
     */
    public function getFullUrlAttribute()
    {
        if (filter_var($this->file_path, FILTER_VALIDATE_URL)) {
            return $this->file_path;
        }

        return asset('storage/' . $this->file_path);
    }

    /**
     * Получить размер файла в удобочитаемом формате
     *
     * @return string
     */
    public function getReadableSize()
    {
        if (!file_exists(storage_path('app/public/' . $this->file_path))) {
            return 'N/A';
        }

        $size = filesize(storage_path('app/public/' . $this->file_path));

        if ($size < 1024) {
            return $size . ' bytes';
        } elseif ($size < 1024 * 1024) {
            return round($size / 1024, 1) . ' KB';
        } elseif ($size < 1024 * 1024 * 1024) {
            return round($size / (1024 * 1024), 1) . ' MB';
        } else {
            return round($size / (1024 * 1024 * 1024), 1) . ' GB';
        }
    }

    /**
     * Определить MIME-тип файла
     *
     * @return string
     */
    public function getMimeType()
    {
        $extension = pathinfo($this->file_path, PATHINFO_EXTENSION);

        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
            'txt' => 'text/plain',
            // Добавьте другие типы файлов по необходимости
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Проверить, является ли файл изображением
     *
     * @return bool
     */
    public function isImage()
    {
        $extension = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    }
}
