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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            $table->boolean('active');
            $table->string('description', 1024);
            $table->string('stripe_id');
            $table->boolean('shippable')->nullable();
            $table->string('unit_label')->nullable();
            $table->boolean('public');
            $table->foreignId('Tenant');
            $table->foreign('Tenant')
                ->references('ID')
                ->on('tenants')
                ->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('prices', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->foreignUuid('product_id')->references('id')->on('products');
            $table->string('currency', 3)->default('gbp');
            $table->boolean('active');
            $table->string('stripe_id');
            $table->string('nickname');
            $table->string('type');
            $table->integer('unit_amount')->nullable();
            $table->string('billing_scheme');
            $table->string('tax_behavior');
            $table->boolean('usable_in_membership');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');

        Schema::dropIfExists('products');
    }
};
