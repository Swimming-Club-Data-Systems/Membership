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
        Schema::create('competition_guest_entry_header', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->json('custom_form_data');
            $table->foreignId('user_UserID')->nullable();
            $table->foreign('user_UserID')
                ->references('UserID')
                ->on('users')
                ->nullOnDelete();
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
        Schema::dropIfExists('competition_guest_entry_header');
    }
};
