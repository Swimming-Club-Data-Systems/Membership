<?php

namespace App\Models\Tenant;

use App\Enums\BalanceTopUpStatus;
use App\Interfaces\PaidObject;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property int $id
 * @property User $user
 * @property int $amount
 * @property string $currency
 * @property PaymentMethod $paymentMethod
 * @property User $initiator
 * @property Carbon $scheduled_for
 * @property BalanceTopUpStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
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
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who initiated this balance to up. Null if created automatically by the system to clear a balance.
     * Otherwise, may be the user the balance top up belongs to, or a member of club staff who initiated the payment
     * on their behalf.
     *
     * @return BelongsTo
     */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_UserID');
    }

    /**
     * Gets the PaymentMethod for this BalanceTopUp. If the top-up is scheduled for the future, it may be possible to
     * change the payment method to be used before we request the payment from Stripe.
     *
     * @return BelongsTo
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'user';
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

}
