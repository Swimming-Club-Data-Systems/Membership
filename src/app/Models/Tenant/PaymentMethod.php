<?php

namespace App\Models\Tenant;

use App\Models\Central\Tenant;
use App\Traits\BelongsToTenant;
use ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $stripe_id
 * @property string $type
 * @property User $user
 * @property ArrayObject $pm_type_data
 * @property ArrayObject $billing_address
 * @property Tenant $tenant
 * @property boolean $default
 */
class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $casts = [
        'pm_type_data' => AsArrayObject::class,
        'billing_address' => AsArrayObject::class,
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function (self $paymentMethod) {
            // If the type is bacs_debit, and there are no defaults for the user, make this default
            if ($paymentMethod->user && $paymentMethod->type == 'bacs_debit') {
                // Are there any other default bacs_debit methods?
                // If no make this the default
                $otherDefault = $paymentMethod->user->paymentMethods()
                    ->where('type', '=', 'bacs_debit')
                    ->where('default', '=', true)
                    ->where('id', '!=', $paymentMethod->id)
                    ->count();
                if ($otherDefault == 0) {
                    $paymentMethod->default = true;
                }
            } else {
                $paymentMethod->default = false;
            }
        });

        static::saved(function (self $paymentMethod) {
            if ($paymentMethod->default) {
                // Set any others as not default

                $pms = $paymentMethod->user->paymentMethods()
                    ->where('type', '=', 'bacs_debit')
                    ->where('default', '=', true)
                    ->where('id', '!=', $paymentMethod->id)
                    ->get();

                foreach ($pms as $pm) {
                    $pm->default = false;
                    $pm->save();
                }
            }
        });
    }

    public function mandate(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Mandate::class);
    }

    /**
     * Fetch data from Stripe and update the object properties
     * Does not save the model automatically
     *
     * @return void
     */
    public function updateStripeData()
    {
        \Stripe\Stripe::setApiKey(config('cashier.secret'));

        $stripeAccount = $this->tenant->stripeAccount();

        $paymentMethod = \Stripe\PaymentMethod::retrieve([
            'id' => $this->stripe_id,
            'expand' => ['billing_details.address'],
        ], [
            'stripe_account' => $stripeAccount,
        ]);

        /** @var StripeCustomer $customer */
        $customer = StripeCustomer::firstWhere('CustomerID', '=', $paymentMethod->customer);

        $this->stripe_id = $paymentMethod->id;
        $type = $paymentMethod->type;
        $this->type = $type;
        $this->pm_type_data = $paymentMethod->$type;
        $this->billing_address = $paymentMethod->billing_details;
        if ($customer) {
            $this->user()->associate($customer->user);
        }
        $this->created_at = $paymentMethod->created;

        $this->save();
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
