<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MainCoordinatorPageTest extends TestCase
{
    #[Test]
    public function coordinators_page_is_accessible_and_contains_proper_text()
    {
        $response = $this->get('/main-coordinator');

        $response->assertStatus(200);

        $response->assertSee('Koordynatorzy adoracji');
    }
}
