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
        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->boolean('is_system')->default(false);
        });

        Schema::table('journal_accounts', function (Blueprint $table) {
            $table->boolean('is_system')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });

        Schema::table('journal_accounts', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
