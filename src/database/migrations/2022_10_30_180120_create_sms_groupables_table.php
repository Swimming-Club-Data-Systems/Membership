<?php

use App\Models\Tenant\Sms;
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
        Schema::create('sms_groupables', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Sms::class);
            $table->bigInteger('sms_groupable_id');
            $table->string('sms_groupable_type', 128);
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
        Schema::dropIfExists('sms_groupables');
    }
};
