<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstructionPageTest extends TestCase
{
    #[Test]
    public function instruction_page_is_accessible_and_contains_proper_text()
    {
        $response = $this->get('/instruction');

        $response->assertStatus(200);

        $response->assertSee('Instrukcja do aplikacji');

        $response->assertSee('Często zadawane pytania');
    }
}
