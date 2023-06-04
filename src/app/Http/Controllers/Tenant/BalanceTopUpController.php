<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BalanceTopUp;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BalanceTopUpController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        /** @var User $user */
        $user = $request->user();

        $data = $this->indexData($user);

        return Inertia::render('Payments/BalanceTopUps/Index', $data);
    }

    private function indexData(User $user)
    {
        $balanceTopUps = $user
            ->balanceTopUps()
            ->orderBy('created_at', 'desc')
            ->with('paymentMethod')
            ->paginate();

        $balanceTopUps->getCollection()->transform(function (BalanceTopUp $balanceTopUp) {
            return [
                'id' => $balanceTopUp->id,
                'amount' => $balanceTopUp->amount,
                'formatted_amount' => $balanceTopUp->formatted_amount,
                'scheduled_for' => $balanceTopUp->scheduled_for,
                'created_at' => $balanceTopUp->created_at,
                'updated_at' => $balanceTopUp->updated_at,
                'payment_method' => $balanceTopUp->paymentMethod ? [
                    'id' => $balanceTopUp->paymentMethod->id,
                    'description' => $balanceTopUp->paymentMethod->description,
                    'information_line' => $balanceTopUp->paymentMethod->information_line,
                    'type' => $balanceTopUp->paymentMethod->type,
                ] : null,
            ];
        });

        return [
            'balance_top_ups' => $balanceTopUps->onEachSide(3),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
        ];
    }

    public function userIndex(User $user): \Inertia\Response
    {
        $data = $this->indexData($user);

        return Inertia::render('Payments/BalanceTopUps/UserIndex', $data);
    }

    public function new(Request $request, User $user): \Inertia\Response
    {
        /** @var User $authUser */
        $authUser = $request->user();

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $user->preferredDirectDebit();

        return Inertia::render('Payments/BalanceTopUps/New', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'initiator' => [
                'id' => $authUser->id,
                'name' => $authUser->name,
            ],
            'payment_method' => $paymentMethod ? [
                'description' => $paymentMethod->description,
            ] : null,
        ]);
    }

    public function create(Request $request, User $user)
    {
        /** @var User $initiator */
        $initiator = $request->user();

        $validated = $request->validate([
            'scheduled_for' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:+21 days',
            ],
            'amount' => [
                'required',
                'decimal:0,2',
                'min:1',
                'max:1000',
            ],
        ]);

        $paymentMethod = $user->preferredDirectDebit();

        if (! $paymentMethod) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'payment_method' => 'The selected user does not have any usable payment methods.',
            ]);
        }

        $balanceTopUp = new BalanceTopUp();
        $balanceTopUp->user()->associate($user);
        $balanceTopUp->amount_string = $request->string('amount');
        $balanceTopUp->scheduled_for = $request->date('scheduled_for');
        $balanceTopUp->initiator()->associate($initiator);
        $balanceTopUp->paymentMethod()->associate($paymentMethod);
        $balanceTopUp->save();

        $request->session()->flash('flash_bag.success', 'The balance top up has been scheduled successfully.');

        return redirect()->route('users.top_up.index', $user);
    }

    public function show(BalanceTopUp $topUp): \Illuminate\Http\JsonResponse
    {
        return response()->json($topUp->toArray());
    }

    public function userShow(User $user, BalanceTopUp $topUp): \Illuminate\Http\JsonResponse
    {
        return response()->json($topUp->toArray());
    }
}
