<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('competition_event_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('competition_entry_id')->nullable();
            $table->foreign('competition_entry_id', 'competition_entry_id_foreign_ref')
                ->references('id')
                ->on('competition_entries')
                ->cascadeOnDelete();
            $table->foreignId('competition_event_id')->nullable();
            $table->foreign('competition_event_id', 'competition_event_id_foreign_ref')
                ->references('id')
                ->on('competition_events')
                ->cascadeOnDelete();
            $table->decimal('entry_time')->nullable();
            $table->bigInteger('amount')->default(0);
            $table->bigInteger('amount_refunded')->default(0);
            $table->string('cancellation_reason', 255)->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['competition_entry_id', 'competition_event_id'], 'entry_event_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_event_entries');
    }
};
