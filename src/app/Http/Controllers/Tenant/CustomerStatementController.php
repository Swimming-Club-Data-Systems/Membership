<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Accounting\JournalTransaction;
use App\Models\Tenant\CustomerStatement;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerStatementController extends Controller
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

    public function index(Request $request): \Inertia\Response
    {
        /** @var User $user */
        $user = $request->user();

        $statements = $this->getPaginatedStatements($user);

        return Inertia::render('Payments/Statements/Index', [
            'statements' => $statements
        ]);
    }

    private function getPaginatedStatements(User $user): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $statements = $user->statements()->orderBy('end', 'desc')->paginate();

        $statements->getCollection()->transform(function (CustomerStatement $statement) {
            return [
                'id' => $statement->id,
                'start' => $statement->start,
                'end' => $statement->end,
                'opening_balance' => $statement->opening_balance,
                'opening_balance_formatted' => Money::formatCurrency($statement->opening_balance),
                'closing_balance' => $statement->closing_balance,
                'closing_balance_formatted' => Money::formatCurrency($statement->closing_balance),
                'credits' => $statement->credits,
                'credits_formatted' => Money::formatCurrency($statement->credits),
                'debits' => $statement->debits,
                'debits_formatted' => Money::formatCurrency($statement->debits),
            ];
        });

        return $statements;
    }

    public function userStatementIndex(User $user, Request $request): \Inertia\Response
    {
        $this->authorize('viewIndex', CustomerStatement::class);

        $statements = $this->getPaginatedStatements($user);

        return Inertia::render('Payments/Statements/UserIndex', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'statements' => $statements
        ]);
    }

    public function show(Request $request, CustomerStatement $statement): \Inertia\Response
    {
        $this->authorize('view', $statement);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Payments/Statements/Show', $this->getStatementData($statement, $user));
    }

    public function userShow(Request $request, User $user, CustomerStatement $statement): \Inertia\Response
    {
        $this->authorize('view', $statement);

        return Inertia::render('Payments/Statements/UserShow', $this->getStatementData($statement, $user));
    }

    private function getStatementData(CustomerStatement $statement, User $user): array
    {
        $transactions = [];
        foreach ($statement->transactions()->get() as $transaction) {
            /** @var JournalTransaction $transaction */
            $transactions[] = [
                'id' => $transaction->id,
                'debit' => $transaction->debit,
                'debit_formatted' => $transaction->debit ? Money::formatCurrency($transaction->debit, $transaction->currency) : null,
                'credit' => $transaction->credit,
                'credit_formatted' => $transaction->credit ? Money::formatCurrency($transaction->credit, $transaction->currency) : null,
                'currency' => $transaction->currency,
                'memo' => $transaction->memo,
                'posted_at' => $transaction->post_date,
            ];
        }

        return [
            'id' => $statement->id,
            'start' => $statement->start,
            'end' => $statement->end,
            'opening_balance' => $statement->opening_balance,
            'opening_balance_formatted' => Money::formatCurrency($statement->opening_balance),
            'closing_balance' => $statement->closing_balance,
            'closing_balance_formatted' => Money::formatCurrency($statement->closing_balance),
            'credits' => $statement->credits,
            'credits_formatted' => Money::formatCurrency($statement->credits),
            'debits' => $statement->debits,
            'debits_formatted' => Money::formatCurrency($statement->debits),
            'transactions' => $transactions,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ]
        ];
    }
}
