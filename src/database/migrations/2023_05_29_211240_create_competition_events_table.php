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
        Schema::create('competition_events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('stroke', 255);
            $table->string('units', 255);
            $table->bigInteger('distance');
            $table->bigInteger('event_code')->nullable();
            $table->bigInteger('sequence');
            $table->json('ages');
            $table->bigInteger('entry_fee')->default(0);
            $table->bigInteger('processing_fee')->default(0);
            $table->string('category');
            $table->foreignIdFor(\App\Models\Tenant\CompetitionSession::class)
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_events');
    }
};
