<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('point_of_sale_screens', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            $table->foreignId('Tenant');
            $table->foreign('Tenant')
                ->references('ID')
                ->on('tenants')
                ->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('point_of_sale_item_groups', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->foreignUuid('point_of_sale_screen_id')->references('id')->on('point_of_sale_screens');
            $table->string('name')->nullable();
            $table->bigInteger('sequence');
            $table->timestamps();
        });

        Schema::create('point_of_sale_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->foreignUuid('point_of_sale_item_group_id')->references('id')->on('point_of_sale_item_groups');
            $table->string('label')->nullable();
            $table->bigInteger('sequence');
            $table->foreignUuid('price_id')->references('id')->on('prices');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_of_sale_items');

        Schema::dropIfExists('point_of_sale_item_groups');

        Schema::dropIfExists('point_of_sale_screens');
    }
};
