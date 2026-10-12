<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'audit_logs',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('actor_id')
                    ->nullable()
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->string('action', 100);

                $table->string('entity_type', 80)
                    ->nullable();

                $table->unsignedBigInteger('entity_id')
                    ->nullable();

                $table->string('result', 32);

                $table->json('changes')
                    ->nullable();

                $table->char('request_id', 36)
                    ->nullable();

                $table->string('ip', 45)
                    ->nullable();

                $table->string('user_agent', 500)
                    ->nullable();

                $table->dateTime('created_at', 6);

                $table->index([
                    'entity_type',
                    'entity_id',
                    'created_at',
                ]);

                $table->index([
                    'actor_id',
                    'created_at',
                ]);

                $table->index([
                    'action',
                    'created_at',
                ]);
            },
        );

        Schema::create(
            'url_redirects',
            function (Blueprint $table): void {
                $table->id();

                $table->string('entity_type', 32);

                $table->unsignedBigInteger('entity_id');

                $table->string('old_path', 512)
                    ->unique();

                $table->dateTime('created_at', 6);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('url_redirects');
        Schema::dropIfExists('audit_logs');
    }
};
