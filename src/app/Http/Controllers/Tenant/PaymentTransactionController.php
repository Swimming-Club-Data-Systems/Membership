<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Accounting\JournalTransaction;
use App\Models\Tenant\CustomerStatement;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PaymentTransactionController extends Controller
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

    public function index(): \Inertia\Response
    {
        /** @var User $user */
        $user = Auth::user();

        return Inertia::render('Payments/Transactions/Index', $this->getTransactionData($user));
    }

    private function getTransactionData(User $user): array
    {
        $transactions = $user->journal?->transactions()->orderBy('post_date', 'desc')->paginate();

        $transactions->getCollection()->transform(function (JournalTransaction $transaction) {
            return [
                'id' => $transaction->id,
                'debit' => $transaction->debit,
                'debit_formatted' => $transaction->debit ? Money::formatCurrency($transaction->debit, $transaction->currency) : null,
                'credit' => $transaction->credit,
                'credit_formatted' => $transaction->credit ? Money::formatCurrency($transaction->credit, $transaction->currency) : null,
                'currency' => $transaction->currency,
                'memo' => $transaction->memo,
                'posted_at' => $transaction->post_date,
            ];
        });

        return [
            'transactions' => $transactions->onEachSide(3),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
        ];
    }

    public function userIndex(User $user): \Inertia\Response
    {
        $this->authorize('viewIndex', CustomerStatement::class);

        return Inertia::render('Payments/Transactions/UserIndex', $this->getTransactionData($user));
    }
}
