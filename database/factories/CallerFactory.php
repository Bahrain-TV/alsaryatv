<?php

namespace Database\Factories;

use App\Models\Caller;
use Illuminate\Database\Eloquent\Factories\Factory;

class CallerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Caller::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'cpr' => $this->faker->unique()->numerify('##########'),

            'is_winner' => false,
            'ip_address' => $this->faker->ipv4(),
            'hits' => $this->faker->numberBetween(1, 100),
            'last_hit' => $this->faker->dateTimeThisYear(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'blocked']),
            'notes' => $this->faker->optional(0.7)->sentence(),
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }

    /**
     * Indicate that the caller is a winner.
     */
    public function winner(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_winner' => true,
            ];
        });
    }
}
