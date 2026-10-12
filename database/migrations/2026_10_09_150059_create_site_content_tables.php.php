<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'site_pages',
            function (Blueprint $table): void {
                $table->id();

                $table->string('key', 60)
                    ->unique();

                $table->string('title', 200);

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

                $table->unsignedInteger('lock_version')
                    ->default(1);

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);
            },
        );

        Schema::create(
            'page_sections',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('page_id')
                    ->constrained('site_pages')
                    ->cascadeOnDelete();

                $table->string('key', 80);
                $table->json('data');

                $table->boolean('is_enabled')
                    ->default(true);

                $table->integer('sort_order')
                    ->default(0);

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);

                $table->unique([
                    'page_id',
                    'key',
                ]);
            },
        );

        Schema::create(
            'site_settings',
            function (Blueprint $table): void {
                $table->id();

                $table->string('key', 100)
                    ->unique();

                $table->json('value');

                $table->foreignId('updated_by')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->unsignedInteger('lock_version')
                    ->default(1);

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);
            },
        );

        Schema::create(
            'offices',
            function (Blueprint $table): void {
                $table->id();

                $table->string('name', 150);
                $table->text('address');

                $table->string('phone', 32)
                    ->nullable();

                $table->string('email', 254)
                    ->nullable();

                $table->decimal(
                    'latitude',
                    10,
                    7,
                )->nullable();

                $table->decimal(
                    'longitude',
                    10,
                    7,
                )->nullable();

                $table->json('hours')
                    ->nullable();

                $table->boolean('is_head_office')
                    ->default(false);

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
    }

    public function down(): void
    {
        Schema::dropIfExists('offices');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('page_sections');
        Schema::dropIfExists('site_pages');
    }
};
