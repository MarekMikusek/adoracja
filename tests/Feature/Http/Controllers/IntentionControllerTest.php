<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Intention;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IntentionControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_guest_sees_only_confirmed_intentions(): void
    {
        // GIVEN
        Intention::factory()->unconfirmed()->create();
        Intention::factory()->count(2)->create(['is_confirmed' => 1]);

        // WHEN
        $response = $this->get(route('intentions'));

        // THEN
        $response->assertStatus(200);
        $response->assertViewHas('intentions', function ($intentions) {
            return $intentions->count() === 2; // Tylko potwierdzone
        });
    }

    #[Test]
    public function test_guest_can_save_anonymous_intention(): void
    {
        $payload = ['intention' => 'Modlitwa o zdrowie dla bliskich'];

        $response = $this->postJson(route('intention.save'), $payload);

        $response->assertStatus(201); // Created
        $this->assertDatabaseHas('intentions', [
            'intention' => $payload['intention'],
            'user_id' => null,
        ]);
    }

    #[Test]
    public function test_logged_user_can_join_prayer(): void
    {
        // GIVEN
        $user = User::factory()->create();
        $intention = Intention::factory()->create();

        // WHEN
        $response = $this->actingAs($user)->postJson(route('intentions.is_prayer'), [
            'intention_id' => (string)$intention->id,
            'is_prayer' => '1',
        ]);

        // THEN
        $response->assertStatus(200);
        // Sprawdzamy tabelę intentions_users ze zrzutu SQL
        $this->assertDatabaseHas('intentions_users', [
            'user_id' => $user->id,
            'intention_id' => $intention->id,
        ]);
    }

    #[Test]
    public function test_logged_user_sees_their_own_unconfirmed_intentions(): void
    {
        $user = User::factory()->create();

        // Intencja innego użytkownika (niezatwierdzona) - powinna być ukryta
        Intention::factory()->unconfirmed()->create(['user_id' => User::factory()]);

        // Intencja zalogowanego użytkownika (niezatwierdzona) - powinna być widoczna
        Intention::factory()->unconfirmed()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('intentions'));

        $response->assertStatus(200);
        $response->assertViewHas('intentions', function ($intentions) {
            return $intentions->count() === 1;
        });
    }
}
