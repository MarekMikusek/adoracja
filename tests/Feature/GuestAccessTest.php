<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuestAccessTest extends TestCase
{
    use RefreshDatabase;
    
    #[Test]
    public function test_guest_can_view_home_page_with_correct_elements(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);

        $response->assertSee('Adoracja w najbliższym czasie');
        $response->assertSee('Zaloguj się');
        $response->assertSee('Zarejestruj się');
        $response->assertSee('Kontakt do koorynatorów');

        $response->assertSee('Ilość osób adorujących w najbliższym czasie');

        $response->assertDontSee('Wyloguj się');
        $response->assertDontSee('Moje konto');
        $response->assertDontSee('Moja deklaracja posługi');

        $response->assertDontSee('znaczenie kolorów:');
    }

    #[Test]
    public function test_guest_cannot_access_store_duty_route(): void
    {
        $response = $this->post(route('current-duty.store'), [
            'duty_id'   => 1,
            'duty_type' => 'adoracja',
        ]);

        $response->assertRedirect(route('login'));
    }
}
