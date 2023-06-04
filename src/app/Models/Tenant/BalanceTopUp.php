<?php

namespace App\Models\Tenant;

use App\Business\Helpers\Money;
use App\Enums\BalanceTopUpStatus;
use App\Interfaces\PaidObject;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property int $id
 * @property User $user
 * @property int $amount
 * @property string $formatted_amount
 * @property string $currency
 * @property PaymentMethod $paymentMethod
 * @property PaymentLine $paymentLine
 * @property User $initiator
 * @property Carbon $scheduled_for
 * @property BalanceTopUpStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $amount_string
 */
class BalanceTopUp extends Model implements PaidObject
{
    use HasFactory, BelongsToPrimaryModel;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => BalanceTopUpStatus::class,
        'scheduled_for' => 'datetime',
    ];

    /**
     * Get the user who this balance to up belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who initiated this balance to up. Null if created automatically by the system to clear a balance.
     * Otherwise, may be the user the balance top up belongs to, or a member of club staff who initiated the payment
     * on their behalf.
     */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_UserID');
    }

    /**
     * Gets the PaymentMethod for this BalanceTopUp. If the top-up is scheduled for the future, it may be possible to
     * change the payment method to be used before we request the payment from Stripe.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function paymentLine(): MorphOne
    {
        return $this->morphOne(PaymentLine::class, 'associated');
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'user';
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

    public function handlePaid(): void
    {
        $this->status = BalanceTopUpStatus::COMPLETE;
        $this->save();
    }

    public function handleChargedBack(): void
    {
        // TODO: Implement handleChargedBack() method.
    }

    public function handleRefund(int $refundAmount): void
    {
        // TODO: Implement handleRefund() method.
    }

    public function getPaymentLineDescriptor(): string
    {
        return 'Payment to Account Balance';
    }

    public function handleFailed(): void
    {
        $this->status = BalanceTopUpStatus::FAILED;
        $this->save();
    }

    public function handleCanceled(): void
    {
        $this->status = BalanceTopUpStatus::FAILED;
        $this->save();
    }

    /**
     * Get or set the credit amount as a string.
     */
    protected function amountString(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => (string) BigDecimal::of((string) $attributes['amount'])->withPointMovedLeft(2),
            set: fn ($value, $attributes) => [
                'amount' => BigDecimal::of($value)->withPointMovedRight(2)->toInt(),
            ],
        );
    }
}
