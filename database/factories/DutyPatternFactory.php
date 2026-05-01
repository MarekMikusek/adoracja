<?php

namespace Database\Factories;

use App\Models\DutyPattern;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DutyPatternFactory extends Factory
{
    /**
     * Nazwa modelu, z którym powiązana jest fabryka.
     *
     * @var string
     */
    protected $model = DutyPattern::class;

    /**
     * Definicja stanu domyślnego modelu.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'         => User::factory(), // Tworzy nowego użytkownika dla każdego wzorca
            'day'             => $this->faker->randomElement([
                'Poniedziałek',
                'Wtorek',
                'Środa',
                'Czwartek',
                'Piątek',
                'Sobota',
                'Niedziela'
            ]),
            'hour'            => $this->faker->numberBetween(0, 23),
            'duty_type'       => $this->faker->randomElement(['adoracja', 'rezerwa']),
            'repeat_interval' => $this->faker->randomElement([1, 1, 2, 3]), // Najczęściej 1, rzadziej 2 lub 3
            'start_date'      => $this->faker->optional(0.9)->dateTimeBetween('-1 month', '+1 month')?->format('Y-m-d'),
            'added_by'        => null, // Domyślnie null, jak w większości rekordów z SQL
        ];
    }

    /**
     * Stan dla konkretnego typu dyżuru: adoracja.
     */
    public function adoracja(): static
    {
        return $this->state(fn (array $attributes) => [
            'duty_type' => 'adoracja',
        ]);
    }

    /**
     * Stan dla konkretnego typu dyżuru: rezerwa.
     */
    public function rezerwa(): static
    {
        return $this->state(fn (array $attributes) => [
            'duty_type' => 'rezerwa',
        ]);
    }
}
