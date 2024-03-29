<?php

declare(strict_types=1);

use App\Models\Accounting\Ledger;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingJournalsTable extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Ledger::class, 'ledger_id')->nullable();
            $table->bigInteger('balance');
            $table->string('currency', 5);
            $table->string('morphed_type', 128);
            $table->bigInteger('morphed_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_journals');
    }
}
