<?php

namespace App\DTO\Briefs;

use App\Http\Requests\Briefs\AnswerRequest;
use Illuminate\Support\Collection;

class BriefAnswerDTO
{
    public function __construct(
        public readonly Collection $answers,
    )
    {
    }

    public static function fromAnswerRequest(AnswerRequest $request): self
    {
        return new self(
            answers: self::prepareAnswers($request->validated('answers')),
        );
    }

    public static function fromStoreRoomsRequest(AnswerRequest $request): self
    {
        return new self(
            answers: self::prepareRooms($request->validated('rooms')),
        );
    }


    private static function prepareRooms(array $rooms): Collection
    {
        $preparedRooms = collect();

        foreach ($rooms as $questionKey => $answers) {
            foreach ($answers as $roomId => $answer) {
                $preparedRooms->push(['room_id' => $roomId, 'question_key' => $questionKey, 'answer_text' => $answer]);
            }
        }

        return $preparedRooms;
    }

    private static function prepareAnswers(array $answers): Collection
    {
        $preparedAnswers = collect();

        foreach ($answers as $questionKey => $answer) {
           $preparedAnswers->push(['question_key' => $questionKey, 'answer_text' => $answer]);
        }

        return  $preparedAnswers;
    }


}
