<?php

namespace Database\Factories;

use App\Models\Brief;
use App\Models\BriefAnswer;
use App\Models\BriefRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

class BriefAnswerFactory extends Factory
{
    protected $model = BriefAnswer::class;

    public function definition(): array
    {
        return [
            'brief_id' => Brief::factory(),
            'room_id' => null,
            'question_key' => 'question_' . $this->faker->numberBetween(1, 50),
            'answer_text' => $this->faker->paragraph(),
            'answer_json' => null,
        ];
    }

    public function forBrief(Brief $brief): static
    {
        return $this->state(fn (array $attributes) => [
            'brief_id' => $brief->id,
        ]);
    }

    public function forRoom(BriefRoom $room): static
    {
        return $this->state(fn (array $attributes) => [
            'room_id' => $room->id,
            'brief_id' => $room->brief_id,
        ]);
    }

    public function withQuestionKey(string $questionKey): static
    {
        return $this->state(fn (array $attributes) => [
            'question_key' => $questionKey,
        ]);
    }

    public function shortAnswer(): static
    {
        return $this->state(fn (array $attributes) => [
            'answer_text' => $this->faker->sentence(),
        ]);
    }

    public function longAnswer(): static
    {
        return $this->state(fn (array $attributes) => [
            'answer_text' => $this->faker->paragraphs(3, true),
        ]);
    }
}