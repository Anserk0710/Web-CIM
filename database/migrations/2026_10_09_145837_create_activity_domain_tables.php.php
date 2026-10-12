<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'activities',
            function (Blueprint $table): void {
                $table->id();

                $table->string('public_slug', 180)
                    ->nullable()
                    ->unique();

                $table->string('status', 32)
                    ->default('DRAFT');

                $table->dateTime(
                    'published_at',
                    6,
                )->nullable();

                $table->dateTime(
                    'archived_at',
                    6,
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
                    'published_at',
                    'id',
                ]);
            },
        );

        Schema::create(
            'activity_revisions',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('activity_id')
                    ->constrained('activities')
                    ->restrictOnDelete();

                $table->string('kind', 16);

                $table->string(
                    'requested_slug',
                    180,
                );

                $table->string('title', 200);

                $table->string('excerpt', 500)
                    ->nullable();

                $table->foreignId('category_id')
                    ->nullable()
                    ->constrained('activity_categories')
                    ->restrictOnDelete();

                $table->foreignId(
                    'featured_media_id',
                )
                    ->nullable()
                    ->constrained('media')
                    ->restrictOnDelete();

                $table->boolean('is_featured')
                    ->default(false);

                $table->string('seo_title', 200)
                    ->nullable();

                $table->string(
                    'seo_description',
                    500,
                )->nullable();

                $table->foreignId('og_media_id')
                    ->nullable()
                    ->constrained('media')
                    ->restrictOnDelete();

                $table->foreignId('updated_by')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);

                $table->unique([
                    'activity_id',
                    'kind',
                ]);
            },
        );

        Schema::create(
            'activity_blocks',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('revision_id')
                    ->constrained('activity_revisions')
                    ->cascadeOnDelete();

                $table->char('block_key', 36);
                $table->string('type', 32);
                $table->json('data');

                $table->integer('sort_order')
                    ->default(0);

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);

                $table->unique([
                    'revision_id',
                    'block_key',
                ]);

                $table->index([
                    'revision_id',
                    'sort_order',
                    'id',
                ]);
            },
        );

        Schema::create(
            'activity_revision_related',
            function (Blueprint $table): void {
                $table->foreignId('revision_id')
                    ->constrained('activity_revisions')
                    ->cascadeOnDelete();

                $table->foreignId('product_id')
                    ->constrained('products')
                    ->restrictOnDelete();

                $table->integer('sort_order')
                    ->default(0);

                $table->primary([
                    'revision_id',
                    'product_id',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'activity_revision_related',
        );

        Schema::dropIfExists('activity_blocks');
        Schema::dropIfExists('activity_revisions');
        Schema::dropIfExists('activities');
    }
};
