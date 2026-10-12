<?php

namespace Tests\Feature\Audit;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sensitive_values_are_not_written_to_audit_changes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        app(AuditService::class)->record(
            action: 'test.sensitive',
            entity: $user,
            changes: [
                'name' => 'Safe',
                'password' => 'secret',
                'token' => 'secret-token',
                'cv_path' => 'private/cv.pdf',
            ],
            actor: $user,
        );

        $audit = AuditLog::query()
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(
            'Safe',
            $audit->changes['name'],
        );

        $this->assertArrayNotHasKey(
            'password',
            $audit->changes,
        );

        $this->assertArrayNotHasKey(
            'token',
            $audit->changes,
        );

        $this->assertArrayNotHasKey(
            'cv_path',
            $audit->changes,
        );
    }
}
