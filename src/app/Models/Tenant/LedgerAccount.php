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
 */
class LedgerAccount extends Model
{
    use HasFactory, BelongsToTenant;

    public function ledger(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'ledger_id', 'id');
    }

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
    }
}
