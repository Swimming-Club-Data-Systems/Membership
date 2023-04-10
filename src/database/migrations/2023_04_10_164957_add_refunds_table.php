<?php

use App\Models\Tenant\Refund;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('v2_payment_id');
            $table->foreign('v2_payment_id')
                ->references('id')
                ->on('v2_payments')
                ->cascadeOnDelete();
            $table->string('stripe_id');
            $table->bigInteger('amount');
            $table->string('currency', 3)->default('gbp');
            $table->string('status')->default('pending');
            $table->string('failure_reason')->nullable();
            $table->string('instructions_email')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('reason')->nullable();
            $table->string('description')->nullable();
            $table->foreignIdFor(\App\Models\Central\Tenant::class, 'Tenant');
            $table->foreignId('user_UserID');
            $table->foreign('user_UserID')
                ->references('UserID')
                ->on('users')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->unique('stripe_id');
        });

        Schema::create('refund_v2_payment_line', function (Blueprint $table) {
            $table->foreignId('v2_payment_line_id');
            $table->foreign('v2_payment_line_id')
                ->references('id')
                ->on('v2_payment_lines')
                ->cascadeOnDelete();
            $table->foreignIdFor(Refund::class)->constrained()->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->string('description')->nullable();
            $table->unique(['v2_payment_line_id', 'refund_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_v2_payment_line');

        Schema::dropIfExists('refunds');
    }
};
