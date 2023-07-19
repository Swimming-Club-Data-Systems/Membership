<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('competition_guest_entry_header', 'competition_guest_entry_headers');
        Schema::rename('competition_guest_entrant', 'competition_guest_entrants');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('competition_guest_entrants', 'competition_guest_entrant');
        Schema::rename('competition_guest_entry_headers', 'competition_guest_entry_header');
    }
};
