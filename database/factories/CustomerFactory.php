<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'suscription_id' => $this->faker->numberBetween(1, 3),
            'suscription_active' => $this->faker->boolean(),
            'reference_code' => Str::random(10),
        ];
    }

    public function active() {
        return $this->state(function (array $attrubutes) {
            return [
                'suscription_active' => true,
            ];
        });
    }
    public function inactive() {
        return $this->state(function (array $attrubutes) {
            return [
                'suscription_active' => false,
            ];
        });
    }
}
