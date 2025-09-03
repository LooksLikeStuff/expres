<?php

namespace App\Models;

use App\Services\BriefQuestionKeyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class BriefAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'brief_id',
        'room_id',
        'question_key',
        'answer_text',
        'answer_json',
    ];

    /**
     * Boot метод для настройки событий модели
     */
    protected static function boot()
    {
        parent::boot();

        // При создании или обновлении ответа проверяем и обновляем ключ вопроса
        static::saving(function ($briefAnswer) {
            $briefAnswer->validateAndUpdateQuestionKey();
        });
    }

    /**
     * Проверяет и обновляет ключ вопроса при сохранении
     */
    private function validateAndUpdateQuestionKey()
    {
        if (!$this->question_key || !$this->brief_id) {
            return;
        }

        // Получаем бриф для определения типа
        $brief = $this->brief ?? Brief::find($this->brief_id);
        
        if (!$brief) {
            Log::warning('Не удалось найти бриф для проверки ключа вопроса', [
                'brief_id' => $this->brief_id,
                'question_key' => $this->question_key
            ]);
            return;
        }

        // Используем сервис для получения актуального ключа
        $keyService = app(BriefQuestionKeyService::class);
        $actualKey = $keyService->getActualQuestionKey($this->question_key, $brief->type);

        if ($actualKey !== $this->question_key) {
            Log::info('Обновлен ключ вопроса в BriefAnswer', [
                'brief_id' => $this->brief_id,
                'old_key' => $this->question_key,
                'new_key' => $actualKey
            ]);
            
            $this->question_key = $actualKey;
        }
    }

    /**
     * Отношение к брифу
     */
    public function brief()
    {
        return $this->belongsTo(Brief::class);
    }

    /**
     * Отношение к комнате/зоне
     */
    public function room()
    {
        return $this->belongsTo(BriefRoom::class, 'room_id');
    }

    /**
     * Отношение к вопросу по ключу
     */
    public function question()
    {
        return $this->belongsTo(BriefQuestion::class, 'question_key', 'key');
    }

    /**
     * Статический метод для создания ответа с автоматической проверкой ключа
     *
     * @param array $data
     * @return static
     */
    public static function createWithValidatedKey(array $data): self
    {
        $briefAnswer = new static($data);
        $briefAnswer->validateAndUpdateQuestionKey();
        $briefAnswer->save();
        
        return $briefAnswer;
    }
}
