<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Intention;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Intention>
 */
class IntentionFactory extends Factory
{
    protected $model = Intention::class;

    public function definition(): array
    {
        return [
            'intention' => $this->faker->sentence(10),
            'user_id' => User::factory(), // Domyślnie tworzy powiązanego użytkownika
            'is_confirmed' => $this->faker->boolean(80), // 80% szans na potwierdzenie
        ];
    }

    /**
     * Stan dla intencji dodanej anonimowo przez gościa[cite: 1, 4].
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    /**
     * Stan dla intencji jeszcze niezatwierdzonej[cite: 4].
     */
    public function unconfirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_confirmed' => 0,
        ]);
    }
}
