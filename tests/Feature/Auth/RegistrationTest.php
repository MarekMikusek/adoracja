<?php
namespace Tests\Feature\Auth;

use App\Jobs\AccountRegisteredJob;
use App\Models\User;
use App\Models\WaysOfContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function registration_screen_can_be_rendered(): void
    {
        // Senior tip: Jeśli widok zależy od danych w bazie (WaysOfContact), musimy je stworzyć w teście.
        WaysOfContact::create(['name' => 'Email', 'slug' => 'email']);

        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewHas('waysOfContact');
    }

    #[Test]
    public function new_users_can_register(): void
    {
        Bus::fake();

        $way = WaysOfContact::create(['name' => 'Telefon']);

        $registrationData = [
            'first_name'            => 'Jan',
            'last_name'             => 'Kowalski',
            'email'                 => 'jan@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'ways_of_contacts_id'   => (string) $way->id,
            'rodo_clause'           => 'on',
        ];

        $response = $this->post('/register', $registrationData);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');

        $user = User::where('email', 'jan@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Jan', $user->first_name);

        $this->assertEquals($user->id, $user->added_by);

        $this->assertTrue(Hash::check('Password123!', $user->password));

        Bus::assertDispatched(AccountRegisteredJob::class, function ($job) use ($user) {
            return $job->user->id === $user->id;
        });
    }

    #[Test]
    public function registration_fails_without_rodo_clause(): void
    {
        $response = $this->post('/register', [
            'first_name'            => 'Jan',
            'email'                 => 'jan@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',  
            'ways_of_contacts_id'   => 1,
            // rodo_clause missing
        ]);

        $response->assertSessionHasErrors(['rodo_clause']);
        $this->assertGuest();
    }

}
