<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Payment;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function pay(User $user, Payment $payment): Response
    {
        return $user->id === $payment->user_UserID ? Response::allow() : Response::denyAsNotFound();
    }

}
