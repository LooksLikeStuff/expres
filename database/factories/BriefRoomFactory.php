<?php

namespace Database\Factories;

use App\Models\Brief;
use App\Models\BriefRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

class BriefRoomFactory extends Factory
{
    protected $model = BriefRoom::class;

    public function definition(): array
    {
        $rooms = [
            ['key' => 'room_gostinaya', 'title' => 'Гостиная'],
            ['key' => 'room_kukhnya', 'title' => 'Кухня'],
            ['key' => 'room_spalnya', 'title' => 'Спальня'],
            ['key' => 'room_vannaya', 'title' => 'Ванная комната'],
            ['key' => 'room_prihod', 'title' => 'Прихожая'],
            ['key' => 'room_detskaya', 'title' => 'Детская'],
            ['key' => 'room_kabinet', 'title' => 'Кабинет'],
        ];

        $room = $this->faker->randomElement($rooms);

        return [
            'brief_id' => Brief::factory(),
            'key' => $room['key'],
            'title' => $room['title'],
        ];
    }

    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'custom_' . $this->faker->word(),
            'title' => $this->faker->words(2, true),
        ]);
    }

    public function forBrief(Brief $brief): static
    {
        return $this->state(fn (array $attributes) => [
            'brief_id' => $brief->id,
        ]);
    }
}
