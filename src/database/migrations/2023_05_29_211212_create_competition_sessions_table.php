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
        Schema::create('competition_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->bigInteger('sequence');
            $table->dateTimeTz('start_time');
            $table->dateTimeTz('end_time');
            $table->foreignIdFor(\App\Models\Tenant\Venue::class)
                ->nullable()
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignIdFor(\App\Models\Tenant\Competition::class)
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
        Schema::dropIfExists('competition_sessions');
    }
};
