<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();

            $table->string('public_slug', 180)
                ->nullable()
                ->unique();

            $table->string('status', 32)
                ->default('DRAFT');

            $table->dateTime('published_at', 6)
                ->nullable();

            $table->dateTime('archived_at', 6)
                ->nullable();

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
        });

        Schema::create(
            'product_revisions',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('product_id')
                    ->constrained('products')
                    ->restrictOnDelete();

                $table->string('kind', 16);

                $table->string(
                    'requested_slug',
                    180,
                );

                $table->string('name', 200);

                $table->string('summary', 500)
                    ->nullable();

                $table->text('description')
                    ->nullable();

                $table->foreignId('category_id')
                    ->nullable()
                    ->constrained('product_categories')
                    ->restrictOnDelete();

                $table->foreignId('brand_id')
                    ->nullable()
                    ->constrained('brands')
                    ->restrictOnDelete();

                $table->foreignId('type_id')
                    ->nullable()
                    ->constrained('product_types')
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
                    'product_id',
                    'kind',
                ]);

                $table->index([
                    'kind',
                    'category_id',
                    'brand_id',
                    'type_id',
                ]);
            },
        );

        Schema::create(
            'product_revision_media',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('revision_id')
                    ->constrained('product_revisions')
                    ->cascadeOnDelete();

                $table->foreignId('media_id')
                    ->constrained('media')
                    ->restrictOnDelete();

                $table->string('role', 16);

                $table->integer('sort_order')
                    ->default(0);

                $table->string('alt_text', 500)
                    ->nullable();

                $table->string('caption', 1000)
                    ->nullable();

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);

                $table->unique([
                    'revision_id',
                    'media_id',
                    'role',
                ]);

                $table->index([
                    'revision_id',
                    'role',
                    'sort_order',
                ]);
            },
        );

        Schema::create(
            'product_highlights',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('revision_id')
                    ->constrained('product_revisions')
                    ->cascadeOnDelete();

                $table->string('text', 500);

                $table->integer('sort_order')
                    ->default(0);

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);

                $table->index([
                    'revision_id',
                    'sort_order',
                    'id',
                ]);
            },
        );

        Schema::create(
            'spec_groups',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('revision_id')
                    ->constrained('product_revisions')
                    ->cascadeOnDelete();

                $table->string('name', 150);

                $table->integer('sort_order')
                    ->default(0);

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);

                $table->index([
                    'revision_id',
                    'sort_order',
                    'id',
                ]);
            },
        );

        Schema::create(
            'spec_items',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('group_id')
                    ->constrained('spec_groups')
                    ->cascadeOnDelete();

                $table->string('label', 150);
                $table->text('value');

                $table->string('unit', 50)
                    ->nullable();

                $table->integer('sort_order')
                    ->default(0);

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);

                $table->index([
                    'group_id',
                    'sort_order',
                    'id',
                ]);
            },
        );

        Schema::create(
            'product_revision_related',
            function (Blueprint $table): void {
                $table->foreignId('revision_id')
                    ->constrained('product_revisions')
                    ->cascadeOnDelete();

                $table->foreignId('related_product_id')
                    ->constrained('products')
                    ->restrictOnDelete();

                $table->integer('sort_order')
                    ->default(0);

                $table->primary([
                    'revision_id',
                    'related_product_id',
                ]);
            },
        );

        Schema::create(
            'ecatalog_entries',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('product_id')
                    ->constrained('products')
                    ->restrictOnDelete();

                $table->string(
                    'catalog_code',
                    120,
                )->unique();

                $table->string(
                    'procurement_title',
                    200,
                );

                $table->string(
                    'procurement_category',
                    150,
                )->nullable();

                $table->text('notes')
                    ->nullable();

                $table->string('unit_package', 150)
                    ->nullable();

                $table->string('availability', 32)
                    ->default('CONTACT_US');

                $table->string(
                    'reference_url',
                    2048,
                )->nullable();

                $table->dateTime('effective_at', 6)
                    ->nullable();

                $table->dateTime('expires_at', 6)
                    ->nullable();

                $table->string('status', 32)
                    ->default('DRAFT');

                $table->integer('sort_order')
                    ->default(0);

                $table->string('seo_title', 200)
                    ->nullable();

                $table->string(
                    'seo_description',
                    500,
                )->nullable();

                $table->dateTime('published_at', 6)
                    ->nullable();

                $table->unsignedInteger('lock_version')
                    ->default(1);

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);

                $table->index([
                    'status',
                    'product_id',
                ]);

                $table->index('expires_at');
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('ecatalog_entries');
        Schema::dropIfExists('product_revision_related');
        Schema::dropIfExists('spec_items');
        Schema::dropIfExists('spec_groups');
        Schema::dropIfExists('product_highlights');
        Schema::dropIfExists('product_revision_media');
        Schema::dropIfExists('product_revisions');
        Schema::dropIfExists('products');
    }
};
