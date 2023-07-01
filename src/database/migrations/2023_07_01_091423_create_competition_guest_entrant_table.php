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
        Schema::create('competition_guest_entrant', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->date('date_of_birth');
            $table->string('sex', 255);
            $table->json('custom_form_data');
            $table->foreignUuid('competition_guest_entry_header_id');
            $table->foreign('competition_guest_entry_header_id', 'competition_guest_entry_id_foreign_ref')
                ->references('id')
                ->on('competition_guest_entry_header')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_guest_entrant');
    }
};
