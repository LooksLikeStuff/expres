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
        return $brief->questions()
            ->whereNotIn('key', $brief->answers()->select('question_key'))
            ->min('page');
    }
}


