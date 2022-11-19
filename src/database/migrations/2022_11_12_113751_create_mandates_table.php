<?php

use App\Models\Tenant\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mandates', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id', 255)->unique();
            $table->string('status', 255);
            $table->string('type', 255);
            $table->json('customer_acceptance');
            $table->json('pm_type_details');
            $table->foreignIdFor(PaymentMethod::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mandates');
    }
};
