<?php

declare(strict_types=1);

namespace App\Traits\Accounting;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Exceptions\Accounting\JournalAlreadyExists;
use App\Models\Accounting\Journal;

trait AccountingJournal
{
    public function journal(): MorphOne
    {
        return $this->morphOne(Journal::class, 'morphed');
    }

    /**
     * Initialize a journal for a given model object
     *
     * @param null|string $currency_code
     * @param null|string $ledger_id
     * @return mixed
     * @throws JournalAlreadyExists
     */
    public function initJournal(?string $currency_code = 'GBP', ?int $ledger_id = null)
    {
        if (!$this->journal) {
            $journal = new Journal();
            $journal->ledger_id = $ledger_id;
            $journal->currency = $currency_code;
            $journal->balance = 0;
            return $this->journal()->save($journal);
        }
        throw new JournalAlreadyExists;
    }
}
