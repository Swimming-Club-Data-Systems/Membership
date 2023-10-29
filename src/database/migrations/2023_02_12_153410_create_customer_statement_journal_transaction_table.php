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
        Schema::create('customer_statement_journal_transaction', function (Blueprint $table) {
            $table->foreignId('customer_statement_id');
            $table->foreign('customer_statement_id', 'customer_statement_id_foreign_ref')
                ->references('id')
                ->on('customer_statements')
                ->cascadeOnDelete();
            $table->foreignUuid('journal_transaction_id');
            $table->foreign('journal_transaction_id', 'journal_transaction_id_foreign_ref')
                ->references('id')
                ->on('accounting_journal_transactions')
                ->cascadeOnDelete();
            $table->unique('journal_transaction_id', 'journal_transaction_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_statement_journal_transaction');
    }
};
