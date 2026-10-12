<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSafeTestingEnvironment();
    }

    private function assertSafeTestingEnvironment(): void
    {
        if (! app()->environment('testing')) {
            throw new RuntimeException(
                sprintf(
                    'Unsafe test environment detected: APP_ENV=%s. Tests must run with APP_ENV=testing.',
                    app()->environment(),
                ),
            );
        }

        $connection = (string) config(
            'database.default',
        );

        $database = (string) config(
            "database.connections.{$connection}.database",
        );

        $allowedDatabases = [
            'cim_test',
            'cim_ci',
        ];

        if (! in_array($database, $allowedDatabases, true)) {
            throw new RuntimeException(
                sprintf(
                    'Unsafe test database detected: "%s". Allowed test databases: %s.',
                    $database,
                    implode(', ', $allowedDatabases),
                ),
            );
        }
    }
}
