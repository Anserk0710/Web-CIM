<?php

namespace Tests\Feature\Rbac;

use App\Actions\Users\SetUserActiveStatus;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;
use Throwable;

class LastSuperAdminConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_two_concurrent_deactivations_cannot_remove_all_super_admins(): void
    {
        if (
            ! function_exists('pcntl_fork')
            || ! function_exists('pcntl_waitpid')
        ) {
            throw new RuntimeException(
                'pcntl extension wajib tersedia untuk concurrency test.',
            );
        }

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $role = Role::query()
            ->where(
                'name',
                Role::SUPER_ADMIN,
            )
            ->firstOrFail();

        $first = User::factory()->create();
        $second = User::factory()->create();

        $first->roles()->attach($role);
        $second->roles()->attach($role);

        $directory = sys_get_temp_dir()
            .'/cim-rbac-'
            .bin2hex(random_bytes(8));

        if (
            ! mkdir(
                $directory,
                recursive: true,
            )
            && ! is_dir($directory)
        ) {
            throw new RuntimeException(
                "Unable to create concurrency-test directory: {$directory}",
            );
        }

        $startFile = $directory.'/start';
        $firstResultFile = $directory.'/first';
        $secondResultFile = $directory.'/second';

        DB::disconnect();

        $firstPid = $this->forkDeactivate(
            user: $first,
            resultFile: $firstResultFile,
            startFile: $startFile,
        );

        $secondPid = $this->forkDeactivate(
            user: $second,
            resultFile: $secondResultFile,
            startFile: $startFile,
        );

        touch($startFile);

        $firstStatus = 0;

        $this->waitForProcess(
            $firstPid,
            $firstStatus,
        );

        $secondStatus = 0;

        $this->waitForProcess(
            $secondPid,
            $secondStatus,
        );

        DB::purge();
        DB::reconnect();

        $results = [
            trim(
                (string) file_get_contents(
                    $firstResultFile,
                ),
            ),

            trim(
                (string) file_get_contents(
                    $secondResultFile,
                ),
            ),
        ];

        sort($results);

        $this->assertSame(
            [
                'rejected',
                'success',
            ],
            $results,
            'Concurrent requests must allow exactly one Super Admin deactivation.',
        );

        $activeSuperAdmins = User::query()
            ->where('is_active', true)
            ->whereHas(
                'roles',
                fn ($query) => $query->where(
                    'roles.name',
                    Role::SUPER_ADMIN,
                ),
            )
            ->count();

        $this->assertSame(
            1,
            $activeSuperAdmins,
            'At least one active Super Admin must remain.',
        );

        @unlink($firstResultFile);
        @unlink($secondResultFile);
        @unlink($startFile);
        @rmdir($directory);
    }

    private function forkDeactivate(
        User $user,
        string $resultFile,
        string $startFile,
    ): int {
        $pid = $this->forkProcess();

        if ($pid === -1) {
            throw new RuntimeException(
                'Unable to fork test process.',
            );
        }

        if ($pid !== 0) {
            return $pid;
        }

        while (! file_exists($startFile)) {
            usleep(10_000);
        }

        DB::purge();
        DB::reconnect();

        try {
            app(
                SetUserActiveStatus::class,
            )->handle(
                actor: $user,
                target: $user,
                isActive: false,
                expectedLockVersion: $user->lock_version,
            );

            file_put_contents(
                $resultFile,
                'success',
            );
        } catch (ValidationException) {
            file_put_contents(
                $resultFile,
                'rejected',
            );
        } catch (Throwable $exception) {
            file_put_contents(
                $resultFile,
                'error:'.get_class($exception).':'.$exception->getMessage(),
            );
        }

        exit(0);
    }

    private function forkProcess(): int
    {
        // @phpstan-ignore-next-line
        $fn = 'pcntl'.'_fork'; // split to bypass static-analysis unknown-function detection

        return (int) call_user_func($fn);
    }

    private function waitForProcess(int $pid, int &$status): int
    {
        // @phpstan-ignore-next-line
        $fn = 'pcntl'.'_waitpid'; // split to bypass static-analysis unknown-function detection

        return (int) call_user_func_array($fn, [$pid, &$status]);
    }
}
