<?php

namespace App\Models\Tenant;

use App\Business\Helpers\Money;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property Payment $payment
 * @property User $refunder
 * @property int $amount
 * @property string $formatted_amount
 * @property string $stripe_id
 * @property string $status
 * @property string $currency
 * @property string $description
 * @property string $reason
 * @property string $failure_reason
 * @property string $instructions_email
 * @property string $receipt_number
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Refund extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stripe_id',
        'status',
        'amount',
        'currency',
    ];

    /**
     * The associated payment header information
     */
    public function payment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Payment::class, 'v2_payment_id');
    }

    public function refunder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_UserID');
    }

    public function lines(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(PaymentLine::class,
            'refund_v2_payment_line',
            'refund_id',
            'v2_payment_line_id')
            ->withPivot(['amount', 'description']);
    }

    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => Money::formatCurrency($this->amount, $this->currency),
        );
    }
}
