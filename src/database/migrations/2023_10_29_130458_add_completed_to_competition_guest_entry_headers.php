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
        Schema::table('competition_guest_entry_headers', function (Blueprint $table) {
            $table->boolean('complete')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_guest_entry_headers', function (Blueprint $table) {
            $table->dropColumn('complete');
        });
    }
};
