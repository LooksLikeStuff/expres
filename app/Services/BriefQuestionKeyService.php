<?php

namespace App\Services;

use App\Models\BriefQuestion;
use App\Enums\Briefs\BriefType;
use Illuminate\Support\Facades\Log;

class BriefQuestionKeyService
{
    /**
     * Проверяет и обновляет ключ вопроса если необходимо
     *
     * @param string $questionKey
     * @param BriefType $briefType
     * @return string Актуальный ключ вопроса
     */
    public function getActualQuestionKey(string $questionKey, BriefType $briefType): string
    {
        // Сначала проверяем, существует ли вопрос с таким ключом
        $existingQuestion = BriefQuestion::where('key', $questionKey)
            ->where('brief_type', $briefType)
            ->where('is_active', true)
            ->first();

        if ($existingQuestion) {
            // Ключ актуален, возвращаем как есть
            return $questionKey;
        }

        // Пытаемся найти вопрос по page и order из старого ключа
        $actualKey = $this->findQuestionKeyByPageAndOrder($questionKey, $briefType);

        if ($actualKey) {
            Log::info('Обновлен ключ вопроса', [
                'old_key' => $questionKey,
                'new_key' => $actualKey,
                'brief_type' => $briefType->value
            ]);
            return $actualKey;
        }

        // Если не нашли, возвращаем исходный ключ с предупреждением
        Log::warning('Не удалось найти актуальный ключ вопроса', [
            'question_key' => $questionKey,
            'brief_type' => $briefType->value
        ]);

        return $questionKey;
    }

    /**
     * Находит актуальный ключ вопроса по page и order из старого ключа
     *
     * @param string $oldKey
     * @param BriefType $briefType
     * @return string|null
     */
    private function findQuestionKeyByPageAndOrder(string $oldKey, BriefType $briefType): ?string
    {
        // Извлекаем page и order из ключа
        $pageAndOrder = $this->extractPageAndOrder($oldKey, $briefType);
        
        if (!$pageAndOrder) {
            return null;
        }

        // Ищем вопрос по page и order
        $question = BriefQuestion::where('brief_type', $briefType)
            ->where('page', $pageAndOrder['page'])
            ->where('order', $pageAndOrder['order'])
            ->where('is_active', true)
            ->first();

        return $question ? $question->key : null;
    }

    /**
     * Извлекает page и order из ключа вопроса
     *
     * @param string $key
     * @param BriefType $briefType
     * @return array|null ['page' => int, 'order' => int]
     */
    private function extractPageAndOrder(string $key, BriefType $briefType): ?array
    {
        if ($briefType === BriefType::COMMON) {
            // Для общих брифов: question_2_1 -> page=2, order=1
            if (preg_match('/question_(\d+)_(\d+)/', $key, $matches)) {
                return [
                    'page' => (int)$matches[1],
                    'order' => (int)$matches[2]
                ];
            }
        } elseif ($briefType === BriefType::COMMERCIAL) {
            // Для коммерческих брифов: question_1 -> page=1, order=1 (по умолчанию)
            if (preg_match('/question_(\d+)/', $key, $matches)) {
                return [
                    'page' => (int)$matches[1],
                    'order' => 1 // Для коммерческих брифов обычно один вопрос на страницу
                ];
            }
        }

        return null;
    }

    /**
     * Обновляет ключи в массиве preferences для коммерческих брифов
     *
     * @param array $preferences
     * @return array
     */
    public function updateCommercialPreferencesKeys(array $preferences): array
    {
        $updatedPreferences = [];

        foreach ($preferences as $key => $value) {
            $actualKey = $this->getActualQuestionKey($key, BriefType::COMMERCIAL);
            $updatedPreferences[$actualKey] = $value;
        }

        return $updatedPreferences;
    }

    /**
     * Обновляет ключи в массиве ответов для общих брифов
     *
     * @param array $answers
     * @return array
     */
    public function updateCommonAnswersKeys(array $answers): array
    {
        $updatedAnswers = [];

        foreach ($answers as $key => $value) {
            $actualKey = $this->getActualQuestionKey($key, BriefType::COMMON);
            $updatedAnswers[$actualKey] = $value;
        }

        return $updatedAnswers;
    }

    /**
     * Получает все активные вопросы для типа брифа
     *
     * @param BriefType $briefType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveQuestions(BriefType $briefType)
    {
        return BriefQuestion::where('brief_type', $briefType)
            ->where('is_active', true)
            ->orderBy('page')
            ->orderBy('order')
            ->get();
    }

    /**
     * Проверяет, существует ли вопрос с данным ключом
     *
     * @param string $questionKey
     * @param BriefType $briefType
     * @return bool
     */
    public function questionExists(string $questionKey, BriefType $briefType): bool
    {
        return BriefQuestion::where('key', $questionKey)
            ->where('brief_type', $briefType)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Находит вопрос по page и order
     *
     * @param int $page
     * @param int $order
     * @param BriefType $briefType
     * @return BriefQuestion|null
     */
    public function findQuestionByPageAndOrder(int $page, int $order, BriefType $briefType): ?BriefQuestion
    {
        return BriefQuestion::where('brief_type', $briefType)
            ->where('page', $page)
            ->where('order', $order)
            ->where('is_active', true)
            ->first();
    }
}
