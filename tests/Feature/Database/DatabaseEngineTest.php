<?php

namespace Tests\Feature\Database;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseEngineTest extends TestCase
{
    public function test_integration_database_matches_verified_engine_contract(): void
    {
        $result = DB::selectOne(
            <<<'SQL'
            SELECT
                VERSION() AS database_version,
                @@version_comment AS database_engine,
                @@character_set_server AS server_charset
            SQL
        );

        $engine = (string) config(
            'integration.database.engine',
        );

        $versionPrefix = (string) config(
            'integration.database.version_prefix',
        );

        $combinedEngine = strtolower(
            $result->database_version
            .' '
            .$result->database_engine,
        );

        $this->assertStringContainsString(
            strtolower($engine),
            $combinedEngine,
        );

        if ($versionPrefix !== '') {
            $this->assertStringStartsWith(
                $versionPrefix,
                $result->database_version,
            );
        }

        $this->assertSame(
            'utf8mb4',
            strtolower(
                $result->server_charset,
            ),
        );
    }
}
