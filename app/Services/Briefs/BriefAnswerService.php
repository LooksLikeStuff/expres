<?php

namespace App\Services\Briefs;

use App\DTO\Briefs\BriefAnswerDTO;
use App\Models\Brief;

class BriefAnswerService
{
    public function updateOrCreate(Brief $brief, BriefAnswerDTO $briefAnswerDTO)
    {
        foreach ($briefAnswerDTO->answers as $answer) {
            $brief->answers()->updateOrCreate(
                [
                    'brief_id' => $brief->id,
                    'question_key' => $answer['question_key'], 
                    'room_id' => $answer['room_id'] ?? null
                ],
                $answer);
        }
    }

}
