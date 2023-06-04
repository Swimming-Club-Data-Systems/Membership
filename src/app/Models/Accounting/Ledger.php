<?php

namespace App\Models\Accounting;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Money\Currency;
use Money\Money;

/**
 * @property    int $id
 * @property    Money $balance
 * @property    Carbon $updated_at
 * @property    Carbon $post_date
 * @property    Carbon $created_at
 */
class Ledger extends Model
{
    /**
     * @var string
     */
    protected $table = 'accounting_ledgers';

    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }

    /**
     * Get all of the posts for the country.
     */
    public function journal_transactions(): HasManyThrough
    {
        return $this->hasManyThrough(JournalTransaction::class, Journal::class);
    }

    public function getCurrentBalanceInPounds(): float
    {
        return \App\Business\Helpers\Money::formatCurrency($this->getCurrentBalance('GBP')->getAmount());
    }

    public function getCurrentBalance(string $currency): Money
    {
        if ($this->type == 'asset' || $this->type == 'expense') {
            $balance = $this->journal_transactions->sum('debit') - $this->journal_transactions->sum('credit');
        } else {
            $balance = $this->journal_transactions->sum('credit') - $this->journal_transactions->sum('debit');
        }

        return new Money($balance, new Currency($currency));
    }
}
