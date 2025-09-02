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

    public function fromValidatedCommonRoomsArray(array $rooms): self
    {
        return new self(
            answers: self::prepareRooms($rooms),
        );
    }

    public function fromValidatedCommercialRoomsArray(array $rooms): self
    {
        return new self(
            answers: self::prepareRooms($rooms),
        );
    }

    public static function fromValidatedCommercialAnswersArray(array $answers): self
    {
        return new self(
            answers: self::prepareCommercialAnswers($answers),
        );
    }


    private static function prepareRooms(array $rooms): Collection
    {
        $preparedRooms = collect();

        foreach ($rooms as $questionKey => $answers) {
            foreach ($answers as $roomId => $answer) {
                $data = [
                    'room_id' => $roomId,
                    'question_key' => $questionKey
                ];

                //Если ответ это массив, то сохраняем как json
                if (is_array($answer)) $data['answer_json'] = $answer;
                else $data['answer_text'] = $answer;

                $preparedRooms->push($data);
            }
        }

        return $preparedRooms;
    }

    //Метод для сохранения комнат и описаний для коммерческого брифа
    private static function prepareRoomsAsZones(array $rooms)
    {

    }


    //Метод для сохранения ответов по зонам
    private static function prepareCommercialAnswers(array $answers)
    {

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
