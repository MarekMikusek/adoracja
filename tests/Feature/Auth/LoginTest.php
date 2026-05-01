<?php
namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\WaysOfContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private $wayOfContact;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wayOfContact = WaysOfContact::create(['name' => 'Email']);
    }

    #[Test]
    public function login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    #[Test]
    public function users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret-password'),
        ]);

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'secret-password',
        ]);

        $this->assertAuthenticatedAs($user);

        $response->assertRedirect('/');
    }

    #[Test]
    public function users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function login_requires_email_and_password(): void
    {
        $response = $this->post('/login', [
            'email'    => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    #[Test]
    public function users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();

        $response->assertRedirect('/');

        $this->assertFalse(session()->has('_token_z_poprzedniej_sesji'));
    }

    #[Test]
    public function users_can_authenticate_with_remember_me(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password123',
            'remember' => 'on',
        ]);

        $response->assertCookie(auth()->guard()->getRecallerName());
        $this->assertAuthenticatedAs($user);
    }
}
