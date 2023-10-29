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
        Schema::table('v2_payment_lines', function (Blueprint $table) {
            $table->string('associated_uuid_type')->nullable();
            $table->uuid('associated_uuid_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_payment_lines', function (Blueprint $table) {
            $table->dropColumn('associated_uuid_type');
            $table->dropColumn('associated_uuid_id');
        });
    }
};
