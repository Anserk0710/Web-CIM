<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'inquiries',
            function (Blueprint $table): void {
                $table->id();

                $table->string('type', 32);

                $table->string('name', 150)
                    ->nullable();

                $table->string('email', 254)
                    ->nullable();

                $table->string('phone', 32)
                    ->nullable();

                $table->string(
                    'organization',
                    200,
                )->nullable();

                $table->text('message')
                    ->nullable();

                $table->foreignId('product_id')
                    ->nullable()
                    ->constrained('products')
                    ->restrictOnDelete();

                $table->foreignId(
                    'ecatalog_entry_id',
                )
                    ->nullable()
                    ->constrained('ecatalog_entries')
                    ->restrictOnDelete();

                $table->string('status', 32)
                    ->default('NEW');

                $table->dateTime('resolved_at', 6)
                    ->nullable();

                $table->dateTime(
                    'spam_marked_at',
                    6,
                )->nullable();

                $table->string(
                    'privacy_notice_version',
                    40,
                );

                $table->dateTime(
                    'anonymized_at',
                    6,
                )->nullable();

                $table->unsignedInteger('lock_version')
                    ->default(1);

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);

                $table->index([
                    'status',
                    'created_at',
                    'id',
                ]);

                $table->index([
                    'type',
                    'created_at',
                ]);
            },
        );

        Schema::create(
            'inquiry_notes',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('inquiry_id')
                    ->constrained('inquiries')
                    ->restrictOnDelete();

                $table->foreignId('author_id')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->text('body');

                $table->dateTime('created_at', 6);
                $table->dateTime('updated_at', 6);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiry_notes');
        Schema::dropIfExists('inquiries');
    }
};
