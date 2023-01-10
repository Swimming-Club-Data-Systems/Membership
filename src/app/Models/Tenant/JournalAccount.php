<?php

namespace App\Models\Tenant;

use App\Exceptions\Accounting\JournalAlreadyExists;
use App\Models\Accounting\Journal;
use App\Traits\Accounting\AccountingJournal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property Journal journal
 */
class JournalAccount extends Model
{
    use HasFactory, AccountingJournal;

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
}
