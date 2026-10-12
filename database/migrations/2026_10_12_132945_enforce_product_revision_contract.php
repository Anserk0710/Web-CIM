<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (
            DB::table('product_revisions')
                ->whereNull('category_id')
                ->exists()
        ) {
            throw new RuntimeException(
                'Cannot make product_revisions.category_id required while null revisions exist.',
            );
        }

        Schema::table(
            'product_revisions',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('category_id')
                    ->nullable(false)
                    ->change();
            },
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(
            'product_revisions',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('category_id')
                    ->nullable()
                    ->change();
            },
        );
    }
};
