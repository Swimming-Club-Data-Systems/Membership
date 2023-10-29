<?php

namespace App\Models\Tenant;

use App\Models\Accounting\JournalTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property int $id
 * @property Carbon $start
 * @property Carbon $end
 * @property User $user
 * @property int $opening_balance
 * @property int $closing_balance
 * @property int $credits
 * @property int $debits
 */
class CustomerStatement extends Model
{
    use BelongsToPrimaryModel;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    /**
     * Automatically generate a new statement for a given user
     *
     * @param  User  $user The user to create a statement for
     */
    public static function createStatement(User $user): self
    {
        // Init journal
        $user->getJournal();
        $journal = $user->journal;

        // Set the default start date in case the user has no previous statements
        $start = $user->created_at;

        // Get the latest statement
        $latestStatement = $user->statements()->orderBy('end', 'desc')->first();
        if ($latestStatement) {
            /** @var self $latestStatement */
            $start = $latestStatement->end;
        }

        $statement = new CustomerStatement();
        $statement->start = $start;
        $statement->end = Date::now();
        $statement->user()->associate($user);
        $statement->opening_balance = $journal->getBalanceOn($statement->start)->getAmount();
        $statement->closing_balance = $journal->getBalanceOn($statement->end)->getAmount();

        $credits = $journal->getCreditBalanceOn($statement->end)->getAmount() - $journal->getCreditBalanceOn($statement->start)->getAmount();
        $debits = $journal->getDebitBalanceOn($statement->end)->getAmount() - $journal->getDebitBalanceOn($statement->start)->getAmount();

        $statement->credits = $credits;
        $statement->debits = $debits;
        $statement->save();

        $transactions = $journal->transactions()
            ->where('post_date', '>', $statement->start)
            ->where('post_date', '<=', $statement->end)
            ->get();
        foreach ($transactions as $transaction) {
            $statement->transactions()->attach($transaction);
        }

        return $statement;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(JournalTransaction::class);
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'user';
    }
}
