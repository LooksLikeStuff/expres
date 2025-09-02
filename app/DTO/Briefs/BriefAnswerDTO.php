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

    public static function fromValidatedCommonRoomsArray(array $rooms): self
    {
        return new self(
            answers: self::prepareRooms($rooms),
        );
    }

    public static function fromValidatedCommercialRoomsArray(array $rooms): self
    {
        return new self(
            answers: self::prepareCommercialRooms($rooms),
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

    /**
     * Подготавливает данные для сохранения описаний комнат коммерческого брифа как ответов на вопросы
     */
    private static function prepareCommercialRooms(array $rooms): Collection
    {
        $preparedAnswers = collect();

        foreach ($rooms as $roomId => $roomData) {
                $preparedAnswers->push([
                    'room_id' => $roomId,
                    'question_key' => 'zone_names',
                    'answer_text' => trim($roomData['zone_names']) ?? '-',
                ]);
        }

        return $preparedAnswers;
    }

    /**
     * Подготавливает данные для сохранения описаний новых комнат коммерческого брифа
     */
    public static function fromValidatedCommercialNewRoomsArray(array $addRooms, array $newRoomIds, string $questionKey): self
    {
        $preparedAnswers = collect();

        foreach ($addRooms as $index => $roomData) {
            // Проверяем что у нас есть описание для комнаты и соответствующий ID новой комнаты
            if (isset($roomData['description']) && !empty(trim($roomData['description'])) && isset($newRoomIds[$index])) {
                $preparedAnswers->push([
                    'room_id' => $newRoomIds[$index],
                    'question_key' => $questionKey,
                    'answer_text' => trim($roomData['description']),
                ]);
            }
        }

        return new self(answers: $preparedAnswers);
    }

    /**
     * Метод для сохранения ответов по зонам для других страниц коммерческого брифа
     */
    private static function prepareCommercialAnswers(array $answers): Collection
    {
        $preparedAnswers = collect();

        foreach ($answers as $roomId => $answersData) {
            foreach ($answersData as $questionKey => $roomAnswer) {
                $data = [
                    'room_id' => $roomId,
                    'question_key' => $questionKey
                ];

                // Если ответ это массив, то сохраняем как json
                if (is_array($roomAnswer)) {
                    $data['answer_json'] = $roomAnswer;
                } else {
                    $data['answer_text'] = $roomAnswer;
                }

                $preparedAnswers->push($data);
            }
        }

        return $preparedAnswers;
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
