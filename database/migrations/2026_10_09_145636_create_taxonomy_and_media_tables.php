<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'product_categories',
            function (Blueprint $table): void {
                $table->id();

                $table->string('name', 120);
                $table->string('slug', 160)->unique();

                $table->text('description')
                    ->nullable();

                $table->integer('sort_order')
                    ->default(0);

                $table->boolean('is_active')
                    ->default(true);

                $table->unsignedInteger('lock_version')
                    ->default(1);

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);
            },
        );

        Schema::create('brands', function (Blueprint $table): void {
            $table->id();

            $table->string('name', 120);
            $table->string('slug', 160)->unique();

            $table->boolean('is_active')
                ->default(true);

            $table->unsignedInteger('lock_version')
                ->default(1);

            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);
        });

        Schema::create(
            'product_types',
            function (Blueprint $table): void {
                $table->id();

                $table->string('name', 120);
                $table->string('slug', 160)->unique();

                $table->boolean('is_active')
                    ->default(true);

                $table->unsignedInteger('lock_version')
                    ->default(1);

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);
            },
        );

        Schema::create(
            'activity_categories',
            function (Blueprint $table): void {
                $table->id();

                $table->string('name', 120);
                $table->string('slug', 160)->unique();

                $table->boolean('is_active')
                    ->default(true);

                $table->unsignedInteger('lock_version')
                    ->default(1);

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);
            },
        );

        Schema::create('media', function (Blueprint $table): void {
            $table->id();

            $table->string('disk', 32)
                ->default('public');

            $table->string('path', 512);
            $table->string('original_name', 255);
            $table->string('mime', 100);

            $table->unsignedBigInteger('size_bytes');

            $table->char('sha256', 64);

            $table->unsignedInteger('width')
                ->nullable();

            $table->unsignedInteger('height')
                ->nullable();

            $table->string('default_alt', 500)
                ->nullable();

            $table->text('caption')
                ->nullable();

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->unsignedInteger('lock_version')
                ->default(1);

            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

            $table->unique([
                'disk',
                'path',
            ]);

            $table->index([
                'mime',
                'created_at',
            ]);
        });

        Schema::create(
            'media_usages',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('media_id')
                    ->constrained('media')
                    ->restrictOnDelete();

                $table->string('owner_type', 80);

                $table->unsignedBigInteger('owner_id');

                $table->string('slot', 120);

                $table->unique([
                    'media_id',
                    'owner_type',
                    'owner_id',
                    'slot',
                ]);

                $table->index([
                    'owner_type',
                    'owner_id',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('media_usages');
        Schema::dropIfExists('media');
        Schema::dropIfExists('activity_categories');
        Schema::dropIfExists('product_types');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('product_categories');
    }
};
