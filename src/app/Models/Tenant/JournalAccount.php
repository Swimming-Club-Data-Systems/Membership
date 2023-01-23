<?php

namespace App\Models\Tenant;

use App\Exceptions\Accounting\JournalAlreadyExists;
use App\Models\Accounting\Journal;
use App\Models\Accounting\Ledger;
use App\Traits\Accounting\AccountingJournal;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property Journal journal
 * @property string name
 * @property int id
 * @property Ledger ledger
 */
class JournalAccount extends Model
{
    use HasFactory, BelongsToTenant, AccountingJournal {
        journal as protected traitJournal;
    }

    public Ledger $ledger;

    public function journal(): MorphOne
    {
        if (!$this->traitJournal()) {
            try {
                $this->initJournal();
                $this->refresh();
            } catch (JournalAlreadyExists $e) {
                // Ignore, we already checked existence
            }
        }

        return $this->traitJournal();
    }

    protected static function booted()
    {
        static::created(function (JournalAccount $account) {
            if ($account->ledger != null) {
                $journal = $account->initJournal();
                $journal->assignToLedger($account->ledger);
            }
        });
    }
}
