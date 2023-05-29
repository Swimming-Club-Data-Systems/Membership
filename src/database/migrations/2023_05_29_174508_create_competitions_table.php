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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description');
            $table->string('pool_course');
            $table->string('pool_length');
            $table->foreignIdFor(\App\Models\Tenant\Venue::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->boolean('require_times')->default(false);
            $table->boolean('coach_enters')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('public')->default(true);
            $table->bigInteger('processing_fee')->default(0);
            $table->dateTimeTz('closing_date');
            $table->dateTimeTz('gala_date')->nullable();
            $table->date('age_at_date');
            $table->foreignId('Tenant');
            $table->foreign('Tenant')
                ->references('ID')
                ->on('tenants')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
