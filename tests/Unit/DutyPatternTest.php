<?php

namespace Tests\Unit;

use App\Models\DutyPattern;
use Carbon\Carbon;
use Tests\TestCase;

class DutyPatternTest extends TestCase
{
    /** @test */
    public function it_calculates_intervals_correctly_across_new_year()
    {
        // Tworzymy wzorzec startujący w połowie grudnia, powtarzany co 2 tygodnie
        $pattern = new DutyPattern([
            'start_date' => '2025-12-15', // Poniedziałek, 51. tydzień roku
            'repeat_interval' => 2,
        ]);

        // Tydzień 0 (Start): Powinien być TRUE
        $this->assertTrue($pattern->isDutyInWeek(Carbon::parse('2025-12-15')));

        // Tydzień 1: Powinien być FALSE
        $this->assertFalse($pattern->isDutyInWeek(Carbon::parse('2025-12-22')));

        // Tydzień 2 (Przełom roku - tydzień po 29 grudnia): Powinien być TRUE
        // To jest moment, w którym stare weekOfYear by zawiodło
        $this->assertTrue($pattern->isDutyInWeek(Carbon::parse('2025-12-29')));

        // Tydzień 3 (Styczeń): Powinien być FALSE
        $this->assertFalse($pattern->isDutyInWeek(Carbon::parse('2026-01-05')));

        // Tydzień 4 (Styczeń): Powinien być TRUE
        $this->assertTrue($pattern->isDutyInWeek(Carbon::parse('2026-01-12')));
    }

    /** @test */
    public function it_returns_false_if_date_is_before_start_date()
    {
        $pattern = new DutyPattern([
            'start_date' => '2026-02-01',
            'repeat_interval' => 1,
        ]);

        $this->assertFalse($pattern->isDutyInWeek(Carbon::parse('2026-01-01')));
    }
}
