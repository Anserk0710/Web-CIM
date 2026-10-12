<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'consent_versions',
            function (Blueprint $table): void {
                $table->id();

                $table->string('version', 40)
                    ->unique();

                $table->longText('text');

                $table->string('purpose', 120);

                $table->dateTime(
                    'effective_at',
                    6,
                );

                $table->dateTime('created_at', 6);
            },
        );

        Schema::create(
            'careers',
            function (Blueprint $table): void {
                $table->id();

                $table->string('slug', 180)
                    ->unique();

                $table->string('title', 200);
                $table->string('department', 120);
                $table->string('location', 150);

                $table->string(
                    'employment_type',
                    32,
                );

                $table->text('summary')
                    ->nullable();

                $table->text('responsibilities');
                $table->text('requirements');

                $table->string('status', 32)
                    ->default('DRAFT');

                $table->dateTime('opens_at', 6)
                    ->nullable();

                $table->dateTime('closes_at', 6)
                    ->nullable();

                $table->dateTime('closed_at', 6)
                    ->nullable();

                $table->string('seo_title', 200)
                    ->nullable();

                $table->string(
                    'seo_description',
                    500,
                )->nullable();

                $table->foreignId('created_by')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->foreignId('updated_by')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->unsignedInteger('lock_version')
                    ->default(1);

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);

                $table->index([
                    'status',
                    'closes_at',
                    'id',
                ]);
            },
        );

        Schema::create(
            'career_applications',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('career_id')
                    ->constrained('careers')
                    ->restrictOnDelete();

                $table->string('full_name', 150)
                    ->nullable();

                $table->string('email', 254)
                    ->nullable();

                $table->string(
                    'email_normalized',
                    254,
                )->nullable();

                $table->string('phone', 32)
                    ->nullable();

                $table->text('introduction')
                    ->nullable();

                $table->string(
                    'portfolio_url',
                    2048,
                )->nullable();

                $table->string(
                    'linkedin_url',
                    2048,
                )->nullable();

                $table->string('cv_disk', 32)
                    ->nullable();

                $table->string('cv_path', 512)
                    ->nullable();

                $table->string(
                    'cv_original_name',
                    255,
                )->nullable();

                $table->string('cv_mime', 100)
                    ->nullable();

                $table->unsignedBigInteger(
                    'cv_size',
                )->nullable();

                $table->char('cv_sha256', 64)
                    ->nullable();

                $table->string('status', 32)
                    ->default('NEW');

                $table->foreignId(
                    'consent_version_id',
                )
                    ->nullable()
                    ->constrained('consent_versions')
                    ->restrictOnDelete();

                $table->dateTime(
                    'consented_at',
                    6,
                )->nullable();

                $table->dateTime(
                    'selection_completed_at',
                    6,
                )->nullable();

                $table->dateTime(
                    'retention_due_at',
                    6,
                )->nullable();

                $table->dateTime(
                    'hr_handover_at',
                    6,
                )->nullable();

                $table->dateTime(
                    'anonymized_at',
                    6,
                )->nullable();

                $table->unsignedInteger('lock_version')
                    ->default(1);

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);

                $table->unique([
                    'career_id',
                    'email_normalized',
                ]);

                $table->index([
                    'status',
                    'created_at',
                    'id',
                ]);

                $table->index('retention_due_at');
            },
        );

        Schema::create(
            'application_notes',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('application_id')
                    ->constrained('career_applications')
                    ->restrictOnDelete();

                $table->foreignId('author_id')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->text('body');

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);
            },
        );

        Schema::create(
            'application_status_events',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('application_id')
                    ->constrained('career_applications')
                    ->restrictOnDelete();

                $table->foreignId('actor_id')
                    ->nullable()
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->string(
                    'from_status',
                    32,
                )->nullable();

                $table->string('to_status', 32);

                $table->text('reason')
                    ->nullable();

                $table->dateTime('created_at', 6);

                $table->index([
                    'application_id',
                    'created_at',
                    'id',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'application_status_events',
        );

        Schema::dropIfExists('application_notes');
        Schema::dropIfExists('career_applications');
        Schema::dropIfExists('careers');
        Schema::dropIfExists('consent_versions');
    }
};
