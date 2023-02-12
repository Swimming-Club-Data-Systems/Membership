<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property User $user
 * @property string $stripe_id
 * @property string $stripe_status
 * @property int $amount
 * @property int $amount_refunded
 * @property int $stripe_fee
 * @property int $application_fee_amount
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

    public function paymentIntent() {
        // Create and return payment intent
    }

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

    protected function currency(): Attribute
    {
        return Attribute::make(
            set: fn($value) => Str::lower($value),
        );
    }
}
