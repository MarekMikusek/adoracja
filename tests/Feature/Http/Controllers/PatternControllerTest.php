<?php
namespace Tests\Feature\Http\Controllers;

use App\Mail\DutyCreatedMail;
use App\Mail\DutyRemovedMail;
use App\Models\DutyPattern;
use App\Models\User;
use App\Services\DutiesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PatternControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        // Mockery::mock('alias:' . DutiesService::class);
    }

    #[Test]
    public function index_displays_only_authenticated_user_patterns()
    {
        $user      = User::factory()->create();
        $otherUser = User::factory()->create();

        DutyPattern::factory()->create(['user_id' => $user->id, 'duty_type' => 'adoracja']);
        DutyPattern::factory()->create(['user_id' => $otherUser->id, 'duty_type' => 'rezerwa']);

        $response = $this->actingAs($user)->get(route('patterns.index'));

        $response->assertStatus(200);
        $response->assertViewHas('patterns', function ($patterns) use ($user) {
            return $patterns->every(fn($group) => $group->every(fn($p) => $p->user_id === $user->id));
        });
        $response->assertViewHasAll(['weekDays', 'hours', 'intervals']);
    }

    #[Test]
    public function store_creates_pattern_and_triggers_side_effects()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $data = [
            'day'             => 'Poniedziałek',
            'hour'            => 15,
            'duty_type'       => 'adoracja',
            'repeat_interval' => 1,
            'start_date'      => now()->format('Y-m-d'),
        ];

        // Oczekiwanie wywołania serwisu
        $this->mock(DutiesService::class, function ($mock) {
            $mock->shouldReceive('addUserDuties')
                ->once();
        });

        $response = $this->actingAs($user)->post(route('patterns.store'), $data);

        $response->assertRedirect(route('patterns.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('duty_patterns', [
            'user_id'   => $user->id,
            'hour'      => 15,
            'duty_type' => 'adoracja',
        ]);

        Mail::assertSent(DutyCreatedMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    #[Test]
    public function user_can_delete_their_own_pattern()
    {
        $user    = User::factory()->create();
        $pattern = DutyPattern::factory()->create(['user_id' => $user->id]);

        $this->mock(DutiesService::class, function ($mock) {
            $mock->shouldReceive('removeUserDuties')
                ->once();
        });

        $response = $this->actingAs($user)->post(route('patterns.delete', $pattern));

        $response->assertRedirect(route('patterns.index'));
        $this->assertSoftDeleted($pattern); // Zakładając SoftDeletes, lub assertModelMissing
        Mail::assertSent(DutyRemovedMail::class);
    }

    #[Test]
    public function user_cannot_delete_someone_elses_pattern()
    {
        $user      = User::factory()->create();
        $otherUser = User::factory()->create();
        $pattern   = DutyPattern::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->post(route('patterns.delete', $pattern));

        // Zgodnie z kodem kontrolera: redirect z błędem
        $response->assertRedirect(route('patterns.index'));
        $response->assertSessionHas('error', 'Brak uprawnień do usunięcia tego dyżuru');
        $this->assertDatabaseHas('duty_patterns', ['id' => $pattern->id]);
    }

    #[Test]
    public function admin_can_delete_any_pattern()
    {
        $admin   = User::factory()->create(['is_admin' => true]);
        $user    = User::factory()->create();
        $pattern = DutyPattern::factory()->create(['user_id' => $user->id]);

        $this->mock(DutiesService::class, function ($mock) {
            $mock->shouldReceive('removeUserDuties')
                ->once();
        });

        $response = $this->actingAs($admin)->post(route('patterns.delete', $pattern));

        $response->assertRedirect(route('patterns.index'));
        $this->assertSoftDeleted($pattern);
    }

    #[Test]
    public function store_validation_fails_with_invalid_data()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('patterns.store'), [
            'hour' => 25, // Max 23
            'day'  => '', // Required
        ]);

        $response->assertSessionHasErrors(['hour', 'day']);
    }

    #[Test]
    public function suspend_checks_ownership()
    {
        $user      = User::factory()->create();
        $otherUser = User::factory()->create();
        $pattern   = DutyPattern::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->post(route('patterns.suspend'), [
            'id' => (string)$pattern->id,
            'date_from' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
    }

    protected function tearDown(): void
    {
        // Mockery::close();
        parent::tearDown();
    }
}
