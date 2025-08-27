<?php

namespace App\Services\Briefs;

use App\DTO\Briefs\BriefAnswerDTO;
use App\Models\Brief;

class BriefAnswerService
{
    public function create(Brief $brief, BriefAnswerDTO $briefAnswerDTO)
    {
        foreach ($briefAnswerDTO->answers as $answer) {
            $brief->answers()->create($answer);
        }
    }

}
