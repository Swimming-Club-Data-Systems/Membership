<?php

use App\Models\Tenant\ManualPaymentEntry;
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
        Schema::create('manual_payment_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_UserID')->nullable();
            $table->foreign('user_UserID')
                ->references('UserID')
                ->on('users')
                ->nullOnDelete();
            $table->boolean('posted')->default(false);
            $table->foreignId('Tenant');
            $table->foreign('Tenant')
                ->references('ID')
                ->on('tenants')
                ->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('manual_payment_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ManualPaymentEntry::class)->constrained()->cascadeOnDelete();
            $table->string('description', 255);
            $table->integer('credit')->default(0);
            $table->integer('debit')->default(0);
            $table->foreignId('accounting_journal_id');
            $table->foreign('accounting_journal_id')
                ->references('id')
                ->on('accounting_journals')
                ->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('manual_payment_entry_user', function (Blueprint $table) {
            $table->foreignIdFor(ManualPaymentEntry::class)->constrained()->cascadeOnDelete();
            $table->foreignId('user_UserID');
            $table->foreign('user_UserID')
                ->references('UserID')
                ->on('users')
                ->cascadeOnDelete();
            $table->unique(['manual_payment_entry_id', 'user_UserID'], 'unique_user_and_payment_entry');
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
        Schema::dropIfExists('manual_payment_entry_user');
        Schema::dropIfExists('manual_payment_entry_lines');
        Schema::dropIfExists('manual_payment_entries');
    }
};
