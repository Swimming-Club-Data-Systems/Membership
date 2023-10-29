<?php

namespace App\Business\Helpers;

use App\Models\Accounting\Journal;
use App\Traits\Accounting\AccountingJournal;
use App\Traits\BelongsToTenant;
use Illuminate\Support\Carbon;

class QuickFinancialReport
{
    public static function get($modelInstance, $startDate = null, $endDate = null): QuickFinancialReport
    {
        $traits = collect(class_uses($modelInstance))->keys();
        // Check Journal and BelongsToTenant traits
        if (! $traits->contains(AccountingJournal::class)) {
            throw new \Exception('Model class does have the accounting journal trait.');
        }

        // Try and get the journal for this model
        if (! $modelInstance->journal) {
            throw new \Exception('Model instance does not have a journal.');
        }

        /** @var Journal $journal */
        $journal = $modelInstance->journal;

        $start = Carbon::create(2000, 01, 01, null, null, null);
        $end = Carbon::now();

        $utc = new \DateTimeZone('UTC');

        if ($startDate) {
            $start = $startDate;
        }

        if ($endDate) {
            $end = $endDate;
        }

        $startCreditBalance = $journal->getCreditBalanceOn($start);
        $endCreditBalance = $journal->getCreditBalanceOn($end);
        $startDebitBalance = $journal->getDebitBalanceOn($start);
        $endDebitBalance = $journal->getDebitBalanceOn($end);

        $periodCredits = $endCreditBalance->getAmount() - $startCreditBalance->getAmount();
        $periodDebits = $endDebitBalance->getAmount() - $startDebitBalance->getAmount();
        $periodBalance = $periodCredits - $periodDebits;

        return new QuickFinancialReport(
            $start,
            $end,
            Money::formatCurrency($periodCredits, $journal->currency),
            Money::formatCurrency($periodDebits, $journal->currency),
            Money::formatCurrency($periodBalance, $journal->currency),
            $periodCredits,
            $periodDebits,
            $periodBalance,
            $journal->currency,

        );
    }

    private function __construct(
        public $periodStart,
        public $periodEnd,
        public string $periodCreditsFormatted,
        public string $periodDebitsFormatted,
        public string $periodBalanceFormatted,
        public $periodCredits,
        public $periodDebits,
        public $periodBalance,
        public $currency,
    ) {
    }
}
