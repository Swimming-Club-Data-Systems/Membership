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
        Schema::create('v2_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_UserID')
                ->nullable();
            $table->foreign('user_UserID')
                ->references('UserID')
                ->on('users')
                ->nullOnDelete();
            $table->string('stripe_id')->nullable();
            $table->string('stripe_status')->nullable();
            $table->bigInteger('amount')
                ->default(0);
            $table->bigInteger('amount_refunded')
                ->default(0);
            $table->bigInteger('stripe_fee')
                ->default(0);
            $table->bigInteger('application_fee_amount')
                ->default(0);
            $table->foreignIdFor(\App\Models\Tenant\PaymentMethod::class)
                ->nullable()
                ->constrained();
            $table->string('currency', 3)
                ->default('gbp');
            $table->enum('status', ['pending', 'succeeded', 'failed', 'charged_back']);
            $table->string('return_link')
                ->nullable();
            $table->string('cancel_link')
                ->nullable();
            $table->string('return_link_text')
                ->nullable();
            $table->string('cancel_link_text')
                ->nullable();
            $table->string('receipt_email')
                ->nullable();
            $table->foreignId('Tenant');
            $table->foreign('Tenant')
                ->references('ID')
                ->on('tenants')
                ->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('v2_payment_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('v2_payment_id');
            $table->foreign('v2_payment_id')
                ->references('id')
                ->on('v2_payments')
                ->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->bigInteger('unit_amount')->default(0);
            $table->bigInteger('amount_subtotal')->default(0);
            $table->bigInteger('amount_total')->default(0);
            $table->bigInteger('amount_discount')->default(0);
            $table->bigInteger('amount_tax')->default(0);
            $table->bigInteger('amount_refunded')->default(0);
            $table->bigInteger('quantity')->default(0);
            $table->string('currency', 3)->default('gbp');
            $table->string('associated_type')->nullable();
            $table->bigInteger('associated_id')->nullable();
            $table->timestamps();
        });

        Schema::table('balance_top_ups', function (Blueprint $table) {
            $table->string('currency', 3)->default('gbp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('balance_top_ups', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::dropIfExists('v2_payment_lines');

        Schema::dropIfExists('v2_payments');
    }
};
