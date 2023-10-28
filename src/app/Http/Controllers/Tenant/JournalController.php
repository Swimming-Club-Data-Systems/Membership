<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Journal;
use App\Models\Central\Tenant;
use App\Traits\Accounting\AccountingJournal;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class JournalController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function view(Request $request)
    {
        $request->validate([
            'model' => Rule::in(['User']),
            'start' => ['nullable', 'date', 'before:end'],
            'end' => ['nullable', 'date', 'after:start'],
        ]);

        try {
            /** @var Tenant $tenant */
            $tenant = tenant();

            // Construct a query for a model
            /** @var string $model */
            $model = 'App\Models\Tenant\\'.$request->input('model');

            /** @var Model $obj */
            $obj = $model::findOrFail($request->input('id'));

            $traits = collect(class_uses($obj))->keys();
            // Check Journal and BelongsToTenant traits
            if (! ($traits->contains(AccountingJournal::class) && $traits->contains(BelongsToTenant::class))) {
                abort(403, 'This model instance cannot be queried for journal information.');
            }

            // Try and get the journal for this model
            if (! $obj->journal) {
                abort(404, 'Model instance does not have a journal.');
            }

            /** @var Journal $journal */
            $journal = $obj->journal;

            $start = Carbon::create(2000, 01, 01, null, null, null);
            $end = Carbon::now();

            $utc = new \DateTimeZone('UTC');

            if ($request->date('start')) {
                $start = $request->date('start', null, $tenant->timezone);
                $start->setTimezone($utc);
            }

            if ($request->date('end')) {
                $end = $request->date('end', null, $tenant->timezone);
                $end->setTimezone($utc);
            }

            $startCreditBalance = $journal->getCreditBalanceOn($start);
            $endCreditBalance = $journal->getCreditBalanceOn($end);
            $startDebitBalance = $journal->getDebitBalanceOn($start);
            $endDebitBalance = $journal->getDebitBalanceOn($end);

            $periodCredits = $endCreditBalance->getAmount() - $startCreditBalance->getAmount();
            $periodDebits = $endDebitBalance->getAmount() - $startDebitBalance->getAmount();

            return [
                'period_start' => $start,
                'period_end' => $end,
                'period_credits_formatted' => Money::formatCurrency($periodCredits, $journal->currency),
                'period_debits_formatted' => Money::formatCurrency($periodDebits, $journal->currency),
                'period_credits' => $periodCredits,
                'period_debits' => $periodDebits,
                'currency' => $journal->currency,
            ];

            //            return $journal->transactions()->paginate();

        } catch (\Error) {
            abort(404);
        }
    }
}
