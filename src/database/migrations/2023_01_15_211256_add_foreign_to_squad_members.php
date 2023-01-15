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
        Schema::table('squadMembers', function (Blueprint $table) {
            $table->foreign('Member')->references('MemberID')->on('members')->cascadeOnDelete();
            $table->foreign('Squad')->references('SquadID')->on('squads')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('squadMembers', function (Blueprint $table) {
            //
        });
    }
};
