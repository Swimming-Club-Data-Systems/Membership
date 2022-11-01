<?php

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
        Schema::table('meetsWithResults', function (Blueprint $table) {
            $table->primary('Meet');
        });

        Schema::table('meetsWithResults', function (Blueprint $table) {
            $table->bigIncrements('Meet')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meetsWithResults', function (Blueprint $table) {
            $table->unsignedBigInteger('Meet')->change();
        });

        Schema::table('meetsWithResults', function (Blueprint $table) {
            $table->dropPrimary('Meet');
        });
    }
};
