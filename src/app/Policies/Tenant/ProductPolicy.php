<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Product;
use App\Models\Tenant\User;

class ProductPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Perform pre-authorization checks.
     *
     * @param  string  $ability
     * @return void|bool
     */
    public function before(User $user, $ability)
    {
        if ($user->hasPermission('Admin')) {
            return true;
        }
    }

    /**
     * Can the user view the list?
     *
     * @return void|bool
     */
    public function viewAll(User $user)
    {

    }

    public function create(User $user)
    {

    }

    /**
     * Can the current user view the user?
     *
     * @return void|bool
     */
    public function view(User $user, Product $product)
    {

    }
}
