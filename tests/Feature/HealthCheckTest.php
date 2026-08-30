<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_confirms_the_application_and_database_are_available(): void
    {
        $this->get('/up')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }
}
