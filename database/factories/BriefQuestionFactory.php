<?php

namespace Database\Factories;

use App\Enums\Briefs\BriefType;
use App\Models\BriefQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class BriefQuestionFactory extends Factory
{
    protected $model = BriefQuestion::class;

    public function definition(): array
    {
        return [
            'key' => 'question_' . $this->faker->unique()->numberBetween(1, 999),
            'brief_type' => $this->faker->randomElement(BriefType::cases()),
            'title' => $this->faker->sentence(5) . '?',
            'subtitle' => $this->faker->sentence(8),
            'input_type' => $this->faker->randomElement(['text', 'textarea', 'select', 'checkbox']),
            'placeholder' => $this->faker->sentence(4),
            'format' => $this->faker->randomElement(['default', 'faq', 'price']),
            'class' => null,
            'page' => $this->faker->numberBetween(1, 5),
            'order' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }

    public function common(): static
    {
        return $this->state(fn (array $attributes) => [
            'brief_type' => BriefType::COMMON,
        ]);
    }

    public function commercial(): static
    {
        return $this->state(fn (array $attributes) => [
            'brief_type' => BriefType::COMMERCIAL,
        ]);
    }

    public function forPage(int $page): static
    {
        return $this->state(fn (array $attributes) => [
            'page' => $page,
        ]);
    }

    public function withOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order' => $order,
        ]);
    }
}
