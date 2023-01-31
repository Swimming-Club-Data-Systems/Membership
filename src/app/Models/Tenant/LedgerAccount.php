<?php

namespace App\Models\Tenant;

use App\Models\Accounting\Ledger;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string name
 * @property string type
 * @property Ledger ledger
 * @property bool is_system
 */
class LedgerAccount extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function (LedgerAccount $account) {
            $ledger = new Ledger();
            $ledger->name = $account->name;
            $ledger->type = $account->type;
            $ledger->save();
            $account->ledger()->associate($ledger);
        });

        static::saved(function (LedgerAccount $account) {
            $account->ledger->name = $account->name;
            $account->ledger->type = $account->type;
            $account->ledger->save();
        });
    }

    public function ledger(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'ledger_id', 'id');
    }

    public function journalAccounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JournalAccount::class);
    }
}
