<?php

namespace App\Models\Tenant;

use App\Exceptions\Accounting\DebitsAndCreditsDoNotEqual;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
     * @throws ValidationException
     * @throws DebitsAndCreditsDoNotEqual
     */
    public function post()
    {
        if (!($this->lines()->exists() && $this->users()->exists())) {
            throw new ManualPaymentEntryNotReady();
        }

        try {
            DB::beginTransaction();
            foreach ($this->users()->get() as $user) {
                /** @var User $user */

                // Get the user journal
                /** @var Journal $userJournal */
                $user->getJournal();
                $userJournal = $user->journal;

                foreach ($this->lines()->get() as $line) {
                    /** @var ManualPaymentEntryLine $line */
                    $doubleEntryGroup = Accounting::newDoubleEntryTransactionGroup();
                    $amount = Money::GBP($line->getAttribute($line->line_type->value));
                    $doubleEntryGroup->addTransaction($userJournal, $line->line_type->value, $amount, $line->description);
                    $doubleEntryGroup->addTransaction($line->accountingJournal, $line->line_opposite_type->value, $amount, $line->description);
                    $doubleEntryGroup->commit(false);
                }
            }

            $this->posted = true;
            $this->save();

            DB::commit();
        } catch (DebitsAndCreditsDoNotEqual) {
            // Credits and debits are not equal
            // Through a new validation exception

            DB::rollBack();

            throw ValidationException::withMessages([
                'errors' => 'Unable to post transactions. Debits and credits are not equal.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
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

    public function credits(): int
    {
        $total = 0;
        foreach ($this->lines()->get() as $line) {
            /** @var ManualPaymentEntryLine $line */
            $total += $line->credit;
        }
        return $total * $this->users()->count();
    }

    public function debits(): int
    {
        $total = 0;
        foreach ($this->lines()->get() as $line) {
            /** @var ManualPaymentEntryLine $line */
            $total += $line->debit;
        }
        return $total * $this->users()->count();
    }
}
