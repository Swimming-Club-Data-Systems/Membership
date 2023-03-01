<?php

namespace App\Models\Tenant;

use App\Exceptions\Accounting\InvalidJournalEntryValue;
use App\Exceptions\Accounting\InvalidJournalMethod;
use App\Exceptions\Accounting\TransactionCouldNotBeProcessed;
use App\Exceptions\ManualPaymentEntryNotReady;
use App\Models\Accounting\Journal;
use App\Services\Accounting;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Money\Money;

/**
 * @property int $id
 * @property User $user
 * @property boolean $posted
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ManualPaymentEntry extends Model
{
    use HasFactory, Prunable, BelongsToTenant;

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['user', 'users', 'lines'];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'posted' => false,
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('posted', false)->where('created_at', '<=', now()->subHours(3));
    }

    /**
     * @throws InvalidJournalEntryValue
     * @throws InvalidJournalMethod
     * @throws TransactionCouldNotBeProcessed
     * @throws ManualPaymentEntryNotReady
     */
    public function post()
    {
        if (!($this->lines()->exists() && $this->users()->exists())) {
            throw new ManualPaymentEntryNotReady();
        }

        foreach ($this->users()->get() as $user) {
            /** @var User $user */

            // Get the user journal
            /** @var Journal $userJournal */
            $userJournal = $user->getJournal();

            foreach ($this->lines()->get() as $line) {
                /** @var ManualPaymentEntryLine $line */
                $doubleEntryGroup = Accounting::newDoubleEntryTransactionGroup();
                $amount = Money::GBP($line->getAttribute($line->line_type));
                $doubleEntryGroup->addTransaction($userJournal, $line->line_type, $amount, $line->description);
                $doubleEntryGroup->addTransaction($line->accountingJournal, $line->line_opposite_type, $amount, $line->description);
                $doubleEntryGroup->commit();
            }
        }
    }

    public function lines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ManualPaymentEntryLine::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
