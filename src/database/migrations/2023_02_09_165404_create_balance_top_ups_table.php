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
        Schema::create('balance_top_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_UserID');
            $table->foreign('user_UserID')
                ->references('UserID')
                ->on('users');
            $table->bigInteger('amount');
            $table->foreignIdFor(\App\Models\Tenant\PaymentMethod::class)->nullable()->constrained();
            $table->foreignId('initiator_UserID')->nullable();
            $table->foreign('initiator_UserID')
                ->references('UserID')
                ->on('users')
                ->nullOnDelete();
            $table->dateTime('scheduled_for')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'complete', 'failed'])->default('pending');
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
        Schema::dropIfExists('balance_top_ups');
    }
};
