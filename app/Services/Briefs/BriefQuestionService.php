<?php

namespace App\Services\Briefs;

use App\DTO\Briefs\BriefQuestionDTO;
use App\Enums\Briefs\BriefType;
use App\Models\Brief;
use App\Models\BriefQuestion;

class BriefQuestionService
{
    public function updateOrCreate(BriefQuestionDTO $briefQuestionDTO): BriefQuestion
    {
        return BriefQuestion::updateOrCreate(
            [
                'key' => $briefQuestionDTO->key,
                'brief_type' => $briefQuestionDTO->briefType
            ],
            $briefQuestionDTO->toArray());
    }

    public function ge()
    {

    }

    public function getQuestionsByTypeAndPage(BriefType $type, int $page = 1)
    {
        return  BriefQuestion::query()
            ->where('brief_type', $type)
            ->where('page', $page)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    public function getMinSkippedPage(Brief $brief)
    {
        $answeredKeys = $brief->answers()->pluck('question_key')->toArray();

        $questions = $brief->questions()
            ->whereNotIn('key', $answeredKeys)
            ->orderBy('page')
            ->get();

        if ($questions->isEmpty()) {
            return null;
        }

        $minPage = $questions->first()->page;

        // Берем все вопросы с минимальным page
        $minPageQuestions = $questions->where('page', $minPage);

        $keys = $minPageQuestions->pluck('key')->toArray();

        // Проверяем, есть ли оба ключа в массиве, значит третья страница была пропущена
        if ($minPage === 3
            && in_array('room_custom', $keys)
            && in_array('room_default', $keys)
        ) {
            return $minPage;
        }

        // Если осталась только одна из них, возвращаем null
        return null;
    }
}


