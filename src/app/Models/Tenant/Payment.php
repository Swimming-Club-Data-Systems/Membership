<?php

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property User $user
 * @property int $amount
 * @property int $amount_refunded
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
    use HasFactory;

    protected $table = 'v2_payments';

    public function paymentIntent() {
        // Create and return payment intent
    }

    public function lines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentLine::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function currency(): Attribute
    {
        return Attribute::make(
            set: fn($value) => Str::lower($value),
        );
    }
}
