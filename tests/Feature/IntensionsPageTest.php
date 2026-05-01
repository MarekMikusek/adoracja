<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IntensionsPageTest extends TestCase
{
    #[Test]
    public function instruction_page_is_accessible_and_contains_proper_text()
    {
        $response = $this->get('/rodo');

        $response->assertStatus(200);

        $response->assertSee('Intencje modlitewne');
    }
}
