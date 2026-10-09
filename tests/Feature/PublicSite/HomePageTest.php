<?php

namespace Tests\Feature\PublicSite;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_public_homepage_is_available(): void
    {
        $this->withoutVite();

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('PT Cahya Intan Medika')
            ->assertSee('Laravel 13');
    }
}
