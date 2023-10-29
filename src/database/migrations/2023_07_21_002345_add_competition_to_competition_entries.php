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
        Schema::table('competition_entries', function (Blueprint $table) {
            $table->foreignId('competition_id');
            $table->foreign('competition_id', 'competition_id_foreign_ref')
                ->references('id')
                ->on('competitions')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_entries', function (Blueprint $table) {
            $table->dropForeign('competition_id_foreign_ref');
            $table->dropColumn('competition_id');
        });
    }
};
