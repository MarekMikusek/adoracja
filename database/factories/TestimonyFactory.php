<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Testimony;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Testimony>
 */
class TestimonyFactory extends Factory
{
    /**
     * Model, z którym powiązana jest fabryka.
     *
     * @var string
     */
    protected $model = Testimony::class;

    /**
     * Definicja domyślnego stanu modelu.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nickname'     => $this->faker->name(),
            'testimony'    => $this->faker->paragraph(3), // Generuje tekst świadectwa[cite: 1, 2]
            'is_confirmed' => false, // Domyślnie niepotwierdzone, zgodnie ze strukturą bazy[cite: 3]
            'created_at'   => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at'   => now(),
        ];
    }

    /**
     * Stan: Świadectwo jest już zatwierdzone.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_confirmed' => true,
        ]);
    }

    /**
     * Stan: Świadectwo oczekuje na moderację.
     */
    public function unconfirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_confirmed' => false,
        ]);
    }
}
