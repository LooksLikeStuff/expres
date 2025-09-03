<?php

namespace Database\Factories;

use App\Enums\Briefs\BriefStatus;
use App\Enums\Briefs\BriefType;
use App\Models\Brief;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BriefFactory extends Factory
{
    protected $model = Brief::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(BriefType::cases()),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(BriefStatus::cases()),
            'article' => $this->faker->unique()->word(),
            'price' => $this->faker->numberBetween(50000, 2000000),
            'total_area' => $this->faker->numberBetween(30, 200),
            'zones' => null,
            'preferences' => null,
        ];
    }

    public function common(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BriefType::COMMON,
        ]);
    }

    public function commercial(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BriefType::COMMERCIAL,
            'zones' => json_encode([
                [
                    'name' => $this->faker->words(2, true),
                    'description' => $this->faker->sentence(),
                    'total_area' => $this->faker->numberBetween(10, 50),
                    'projected_area' => $this->faker->numberBetween(8, 45),
                ]
            ]),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BriefStatus::COMPLETED,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BriefStatus::ACTIVE,
        ]);
    }
}
