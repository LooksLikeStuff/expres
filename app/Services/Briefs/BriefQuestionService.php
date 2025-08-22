<?php

namespace App\Services\Briefs;

use App\DTO\Briefs\BriefQuestionDTO;
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
}


