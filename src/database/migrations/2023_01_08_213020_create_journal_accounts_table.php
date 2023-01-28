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
        Schema::create('journal_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignIdFor(\App\Models\Tenant\LedgerAccount::class);
            $table->foreignIdFor(\App\Models\Central\Tenant::class, 'Tenant');
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
        Schema::dropIfExists('journal_accounts');
    }
};
