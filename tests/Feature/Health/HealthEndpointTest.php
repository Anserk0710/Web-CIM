<?php

namespace Tests\Feature\Health;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_application_health_endpoint_responds_successfully(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
    }

    public function test_database_readiness_endpoint_responds_successfully(): void
    {
        $response = $this->getJson('/ready');

        $response
            ->assertOk()
            ->assertJson([
                'status' => 'ready',
                'application' => 'ok',
                'database' => 'ok',
            ]);
    }
}
