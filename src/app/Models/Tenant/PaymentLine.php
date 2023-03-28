<?php

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property int $id
 * @property Payment $payment
 * @property string $description
 * @property int $unit_amount
 * @property int $amount_subtotal
 * @property int $amount_total
 * @property int $amount_discount
 * @property int $amount_tax
 * @property int $amount_refunded
 * @property int $quantity
 * @property string $currency
 * @property Model $associated
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PaymentLine extends Model
{
    use HasFactory, BelongsToPrimaryModel;

    protected $table = 'v2_payment_lines';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'quantity' => 1,
        'currency' => 'gbp',
    ];

    protected static function boot()
    {
        static::saved(function ($line) {
            $line->payment->amount = $line->payment->lines()->sum('amount_total');
            $line->payment->save();
        });

        parent::boot();
    }

    /**
     * The associated payment header information
     */
    public function payment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Payment::class, 'v2_payment_id');
    }

    /**
     * Get the associated model
     */
    public function associated(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    protected function unitAmount(): Attribute
    {
        return Attribute::make(
            set: fn(int $value, $attributes) => [
                'unit_amount' => $value,
                'amount_total' => $value * ($attributes['quantity'] ?? 0),
            ],
        );
    }

    protected function quantity(): Attribute
    {
        return Attribute::make(
            set: fn(int $value, $attributes) => [
                'quantity' => $value,
                'amount_total' => $value * ($attributes['unit_amount'] ?? 0),
            ],
        );
    }

    protected function currency(): Attribute
    {
        return Attribute::make(
            set: fn($value) => Str::lower($value),
        );
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'payment';
    }
}
