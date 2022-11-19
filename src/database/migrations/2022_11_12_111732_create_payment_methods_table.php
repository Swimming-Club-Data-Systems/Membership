<?php

use App\Models\Central\Tenant;
use App\Models\Tenant\User;
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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id', 255)->unique();
            $table->string('type', 255);
            $table->json('pm_type_data');
            $table->json('billing_address');
            $table->foreignIdFor(User::class)->nullable();
            $table->softDeletes();
            $table->foreignIdFor(Tenant::class, 'Tenant');
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
        Schema::dropIfExists('payment_methods');
    }
};
