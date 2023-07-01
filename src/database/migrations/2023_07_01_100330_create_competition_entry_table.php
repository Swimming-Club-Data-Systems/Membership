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
        Schema::create('competition_entry', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('member_MemberID')->nullable();
            $table->foreign('member_MemberID')
                ->references('MemberID')
                ->on('members')
                ->cascadeOnDelete();
            $table->foreignUuid('competition_guest_entrant_id')->nullable();
            $table->foreign('competition_guest_entrant_id', 'competition_guest_entrant_id_foreign_ref')
                ->references('id')
                ->on('competition_guest_entrant')
                ->cascadeOnDelete();
            $table->boolean('paid')->default(false);
            $table->boolean('processed')->default(false);
            $table->bigInteger('amount')->default(0);
            $table->bigInteger('amount_refunded')->default(0);
            $table->boolean('vetoable')->default(false);
            $table->boolean('approved')->default(false);
            $table->boolean('locked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_entry');
    }
};
