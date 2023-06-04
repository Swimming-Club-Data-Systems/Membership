<?php

namespace App\Models\Tenant;

use App\Business\Helpers\ApplicationFeeAmount;
use App\Business\Helpers\Money;
use App\Enums\PaymentStatus;
use App\Enums\StripePaymentIntentStatus;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_UserID
 * @property User $user
 * @property string $stripe_id
 * @property string $stripe_status
 * @property int $amount
 * @property int $amount_refunded
 * @property string $formatted_amount
 * @property string $formatted_amount_refunded
 * @property int $stripe_fee
 * @property int $application_fee_amount
 * @property int $amount_refundable
 * @property string $formatted_amount_refundable
 * @property PaymentMethod $paymentMethod
 * @property string $currency
 * @property string $status
 * @property string $return_link
 * @property string $cancel_link
 * @property string $return_link_text
 * @property string $cancel_link_text
 * @property string $receipt_email
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Payment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'v2_payments';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'currency' => 'gbp',
        'stripe_status' => StripePaymentIntentStatus::REQUIRES_PAYMENT_METHOD->value,
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'stripe_status' => StripePaymentIntentStatus::class,
    ];

    public function lines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentLine::class, 'v2_payment_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function refunds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Refund::class, 'v2_payment_id')
            ->orderBy('created_at', 'desc');
    }

    public function applicationFeeAmount(): int
    {
        return ApplicationFeeAmount::calculateAmount($this->amount);
    }

    public function paymentIntent(): \Stripe\PaymentIntent
    {
        return \Stripe\PaymentIntent::retrieve([
            'id' => $this->stripe_id,
            'expand' => ['customer', 'payment_method', 'charges.data.balance_transaction'],
        ], [
            'stripe_account' => $this->tenant->stripeAccount(),
        ]);
    }

    /**
     * Determine if the payment can be paid
     */
    public function payable(): bool
    {
        return $this->stripe_status == StripePaymentIntentStatus::REQUIRES_PAYMENT_METHOD;
    }

    protected function currency(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Str::lower($value),
        );
    }

    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => Money::formatCurrency($this->amount, $this->currency),
        );
    }

    protected function formattedAmountRefunded(): Attribute
    {
        return Attribute::make(
            get: fn () => Money::formatCurrency($this->amount_refunded, $this->currency),
        );
    }

    protected function amountRefundable(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount - $this->amount_refunded,
        );
    }

    protected function formattedAmountRefundable(): Attribute
    {
        return Attribute::make(
            get: fn () => Money::formatCurrency($this->amount_refundable, $this->currency),
        );
    }
}
