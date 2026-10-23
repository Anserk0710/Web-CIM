<?php // path: database/migrations/2026_10_23_000001_create_notification_outbox_and_privacy_deletion_ledger_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_outbox', function (Blueprint $table): void {
            $table->id();

            $table->string('event_key', 191)->unique();
            $table->string('event_type', 80);
            $table->string('entity_type', 80);
            $table->unsignedBigInteger('entity_id');

            $table->string('recipient', 254)->nullable();
            $table->json('payload')->nullable();

            $table->string('status', 20)->default('PENDING');
            $table->unsignedSmallInteger('attempts')->default(0);

            $table->dateTime('queued_at', 6)->nullable();
            $table->dateTime('processing_at', 6)->nullable();
            $table->dateTime('next_attempt_at', 6)->nullable();
            $table->dateTime('last_attempt_at', 6)->nullable();
            $table->dateTime('sent_at', 6)->nullable();

            // Hanya simpan error ringkas, bukan stack trace atau payload email.
            $table->text('last_error')->nullable();

            $table->timestamps(6);

            $table->index(['status', 'next_attempt_at', 'id']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('privacy_deletion_ledger', function (Blueprint $table): void {
            $table->id();

            // Entity ID tidak mengandung nama, email, atau data pribadi lainnya.
            $table->string('entity_type', 80);
            $table->unsignedBigInteger('entity_id');
            $table->string('category', 80);
            $table->dateTime('deleted_at', 6);

            $table->unique(
                ['entity_type', 'entity_id', 'category'],
                'privacy_deletion_ledger_entity_unique',
            );

            $table->index(['deleted_at', 'entity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_deletion_ledger');
        Schema::dropIfExists('notification_outbox');
    }
};
