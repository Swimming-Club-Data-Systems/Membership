<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BalanceTopUp;
use App\Models\Tenant\Payment;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function userIndex(User $user): \Inertia\Response
    {
        $data = $this->indexData($user);

        return Inertia::render('Payments/BalanceTopUps/UserIndex', $data);
    }

    private function indexData(User $user) {
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
                ] : null
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
}
