<?php

namespace App\Models\Tenant;

use App\Exceptions\Accounting\JournalAlreadyExists;
use App\Models\Accounting\Journal;
use App\Traits\Accounting\AccountingJournal;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property Journal journal
 * @property string name
 * @property int id
 * @property LedgerAccount ledgerAccount
 */
class JournalAccount extends Model
{
    use HasFactory, BelongsToTenant, AccountingJournal {
        journal as protected traitJournal;
    }

    protected static function booted()
    {
        static::created(function (JournalAccount $account) {
            $journal = $account->initJournal();
            $journal->assignToLedger($account->ledgerAccount->ledger);
        });
    }

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

    public function ledgerAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
    }
}
