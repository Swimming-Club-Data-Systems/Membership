<?php

declare(strict_types=1);

use App\Models\Accounting\Journal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingJournalTransactionsTable extends Migration
{
    /**
     * @var array
     */
    protected $guarded = ['id'];

    public function up(): void
    {
        Schema::create('accounting_journal_transactions', function (Blueprint $table) {
            $table->char('id', 36)->unique()->primary();
            $table->char('transaction_group', 36)->nullable();
            $table->foreignIdFor(Journal::class, 'journal_id');
            $table->bigInteger('debit')->nullable();
            $table->bigInteger('credit')->nullable();
            $table->char('currency', 5);
            $table->text('memo')->nullable();
            $table->text('tags')->nullable();
            $table->char('ref_class', 128)->nullable();
            $table->bigInteger('ref_class_id')->nullable();
            $table->timestamps();
            $table->dateTime('post_date');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_journal_transactions');
    }
}
