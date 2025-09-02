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

    /**
     * Получить ответы по комнатам для общего брифа (страница 3)
     */
    public function getRoomAnswersForCommonBrief(Brief $brief)
    {
        return $brief->answers()
            ->whereHas('question', fn($q) => $q->where('page', 3))
            ->with(['question', 'room'])
            ->get()
            ->groupBy('room_id');
    }

    /**
     * Получить ответы по зонам для коммерческого брифа
     */
    public function getZoneAnswersForCommercialBrief(Brief $brief)
    {
        return $brief->answers()
            ->with(['question', 'room'])
            ->get()
            ->groupBy('room_id');
    }

}
