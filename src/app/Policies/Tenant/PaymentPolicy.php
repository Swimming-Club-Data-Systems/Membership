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

    public function view(User $user, Payment $payment): Response
    {
        if ($user->hasPermission(['Admin'])) {
            return Response::allow();
        }

        return $user->id === $payment->user_UserID ? Response::allow() : Response::denyAsNotFound();
    }

    public function viewIndex(User $user): Response
    {
        if ($user->hasPermission('Admin')) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    public function refund(User $user, Payment $payment): Response
    {
        if ($user->hasPermission(['Admin'])) {
            return Response::allow();
        }

        return $user->id === $payment->user_UserID ? Response::allow() : Response::denyAsNotFound();
    }
}
