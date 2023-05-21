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
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description');
            $table->decimal('long', 9, 6);
            $table->decimal('lat', 8, 6);
            $table->string('website', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->string('google_maps_url', 255)->nullable();
            $table->string('place_id', 255)->nullable();
            $table->string('plus_code_global', 255)->nullable();
            $table->string('plus_code_compound', 255)->nullable();
            $table->string('vicinity', 255)->nullable();
            $table->string('formatted_address', 255)->nullable();
            $table->json('address_components');
            $table->json('html_attributions');
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
        Schema::dropIfExists('venues');
    }
};
