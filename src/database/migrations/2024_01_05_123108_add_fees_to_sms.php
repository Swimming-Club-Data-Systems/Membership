<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sms', function (Blueprint $table) {
            $table->bigInteger('segments_sent')->default(0);
            $table->bigInteger('number_sent')->default(0);
            $table->bigInteger('amount')->default(0);
            $table->string('currency', 3)->default('gbp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms', function (Blueprint $table) {
            $table->dropColumn('number_sent');
            $table->dropColumn('amount');
            $table->dropColumn('currency');
        });
    }
};
