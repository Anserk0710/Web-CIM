<?php

namespace Tests\Feature\PublicSite;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicSiteLayoutDataTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('pages')]
    public function test_public_pages_receive_shared_settings_before_rendering(string $url): void
    {
        $this->withoutVite();

        SiteSetting::query()->create([
            'key' => 'company',
            'value' => ['display_name' => 'Configured CIM Company'],
            'updated_by' => User::factory()->create()->id,
        ]);

        $this->get($url)
            ->assertOk()
            ->assertViewHas('publicSite', fn (array $data): bool => $data['company']['display_name'] === 'Configured CIM Company'
                && $data['offices']->isEmpty(),
            )
            ->assertSee('Configured CIM Company');
    }

    public static function pages(): array
    {
        return [
            'home' => ['/'],
            'company' => ['/company'],
            'contact' => ['/contact'],
        ];
    }
}
