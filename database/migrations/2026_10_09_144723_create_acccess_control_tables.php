<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();

            $table->string('name', 80)->unique();
            $table->string('description', 255)->nullable();

            $table->boolean('is_system')
                ->default(false);

            $table->unsignedInteger('lock_version')
                ->default(1);

            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);
        });

        Schema::create(
            'permissions',
            function (Blueprint $table): void {
                $table->id();

                $table->string('name', 100)->unique();

                $table->string(
                    'description',
                    255,
                )->nullable();

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);
            },
        );

        Schema::create(
            'user_role',
            function (Blueprint $table): void {
                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->foreignId('role_id')
                    ->constrained('roles')
                    ->cascadeOnDelete();

                $table->primary([
                    'user_id',
                    'role_id',
                ]);
            },
        );

        Schema::create(
            'role_permission',
            function (Blueprint $table): void {
                $table->foreignId('role_id')
                    ->constrained('roles')
                    ->cascadeOnDelete();

                $table->foreignId('permission_id')
                    ->constrained('permissions')
                    ->cascadeOnDelete();

                $table->primary([
                    'role_id',
                    'permission_id',
                ]);
            },
        );

        Schema::create(
            'access_guards',
            function (Blueprint $table): void {
                $table->unsignedTinyInteger('id')
                    ->primary();

                $table->string('name', 80)
                    ->unique();
            },
        );

        DB::table('access_guards')->insert([
            'id' => 1,
            'name' => 'super_admin_invariant',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('access_guards');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
