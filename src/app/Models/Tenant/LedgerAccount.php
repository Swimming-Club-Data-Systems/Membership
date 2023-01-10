<?php

namespace App\Models\Tenant;

use App\Models\Accounting\Ledger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string name
 * @property Ledger ledger
 */
class LedgerAccount extends Model
{
    use HasFactory;

    public function journal(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Ledger::class);
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function (LedgerAccount $account) {
            $this->ledger = Ledger::create([
                'name' => $this->name,
                'type' => 'expense'
            ]);
        });
    }
}
