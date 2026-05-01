<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Testimony;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testuje, czy na liście pojawiają się tylko zatwierdzone świadectwa.
     */
    public function test_it_displays_only_confirmed_testimonies(): void
    {
        // GIVEN: Mamy jedno świadectwo zatwierdzone i jedno niezatwierdzone[cite: 1, 3]
        Testimony::factory()->create([
            'nickname' => 'Zatwierdzony Użytkownik',
            'is_confirmed' => true,
        ]);

        Testimony::factory()->create([
            'nickname' => 'Ukryty Użytkownik',
            'is_confirmed' => false,
        ]);

        // WHEN: Użytkownik wchodzi na stronę główną
        $response = $this->get(route('testimonies.index'));

        // THEN: Widzi tylko zatwierdzone treści
        $response->assertStatus(200);
        $response->assertSee('Zatwierdzony Użytkownik');
        $response->assertDontSee('Ukryty Użytkownik');
    }

    /**
     * Testuje proces dodawania świadectwa przez Akcję.
     */
    public function test_it_stores_new_testimony_using_action(): void
    {
        // GIVEN: Dane wejściowe zgodne z StoreTestimonyRequest[cite: 2]
        $data = [
            'nickname' => 'Jan',
            'testimony' => 'Treść nowego świadectwa o długości powyżej limitu.',
        ];

        // WHEN: Wysyłamy żądanie POST
        $response = $this->post(route('testimonies.store'), $data);

        // THEN: Następuje przekierowanie i dane trafiają do bazy jako niepotwierdzone[cite: 2]
        $response->assertRedirect(route('testimonies.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('testimonies', [
            'nickname' => 'Jan',
            'testimony' => 'Treść nowego świadectwa o długości powyżej limitu.',
            'is_confirmed' => false, // Domyślna wartość w Akcji
        ]);
    }

    /**
     * Testuje błędy walidacji w StoreTestimonyRequest.
     */
    public function test_it_fails_validation_when_data_is_incomplete(): void
    {
        // WHEN: Wysyłamy pusty formularz[cite: 2]
        $response = $this->post(route('testimonies.store'), []);

        // THEN: Otrzymujemy błędy dla wymaganych pól[cite: 2]
        $response->assertSessionHasErrors(['nickname', 'testimony']);
    }

    /**
     * Testuje wyświetlanie pojedynczego świadectwa.
     */
    public function test_it_shows_specific_testimony(): void
    {
        // GIVEN: Istniejące świadectwo
        $testimony = Testimony::factory()->create();

        // WHEN: Użytkownik odwiedza stronę szczegółów[cite: 2]
        $response = $this->get(route('testimonies.show', $testimony));

        // THEN: Widzi treść świadectwa
        $response->assertStatus(200);
        $response->assertSee($testimony->nickname);
    }
}
