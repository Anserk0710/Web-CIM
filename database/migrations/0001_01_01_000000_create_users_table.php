<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();

            $table->string('name', 150);
            $table->string('email', 254)->unique();
            $table->dateTime('email_verified_at', 6)->nullable();
            $table->string('password', 255);

            $table->boolean('is_active')
                ->default(true)
                ->index();

            $table->rememberToken();

            $table->unsignedInteger('lock_version')
                ->default(1);

            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);
        });

        Schema::create(
            'password_reset_tokens',
            function (Blueprint $table): void {
                $table->string('email', 254)->primary();
                $table->string('token');
                $table->dateTime('created_at', 6)->nullable();
            },
        );

        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();

            $table->foreignId('user_id')
                ->nullable()
                ->index();

            $table->string('ip_address', 45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->longText('payload');

            $table->integer('last_activity')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
