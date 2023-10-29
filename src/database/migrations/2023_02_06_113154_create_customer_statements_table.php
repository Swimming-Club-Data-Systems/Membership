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
        Schema::create('customer_statements', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start');
            $table->dateTime('end');
            $table->foreignId('user_UserID');
            $table->foreign('user_UserID')
                ->references('UserID')
                ->on('users')
                ->cascadeOnDelete();
            $table->bigInteger('opening_balance');
            $table->bigInteger('closing_balance');
            $table->bigInteger('credits');
            $table->bigInteger('debits');
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
        Schema::dropIfExists('customer_statements');
    }
};
