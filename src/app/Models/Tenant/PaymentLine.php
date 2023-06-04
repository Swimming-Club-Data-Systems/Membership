<?php

namespace App\Models\Tenant;

use App\Business\Helpers\Money;
use App\Interfaces\PaidObject;
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
 * @property int $amount_refundable
 * @property string $formatted_unit_amount
 * @property string $formatted_amount_subtotal
 * @property string $formatted_amount_total
 * @property string $formatted_amount_discount
 * @property string $formatted_amount_tax
 * @property string $formatted_amount_refunded
 * @property string $formatted_amount_refundable
 * @property int $quantity
 * @property string $currency
 * @property Model $associated
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property RefundPaymentLine $pivot
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

    public function refunds(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Refund::class,
            'refund_v2_payment_line',
            'v2_payment_line_id',
            'refund_id')
            ->withPivot(['amount', 'description'])
            ->using(RefundPaymentLine::class);
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'payment';
    }

    protected function unitAmount(): Attribute
    {
        return Attribute::make(
            set: fn (int $value, $attributes) => [
                'unit_amount' => $value,
                'amount_total' => $value * ($attributes['quantity'] ?? 0),
            ],
        );
    }

    protected function quantity(): Attribute
    {
        return Attribute::make(
            set: fn (int $value, $attributes) => [
                'quantity' => $value,
                'amount_total' => $value * ($attributes['unit_amount'] ?? 0),
            ],
        );
    }

    protected function currency(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Str::lower($value),
        );
    }

    protected function formattedUnitAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => Money::formatCurrency($this->unit_amount, $this->currency),
        );
    }

    protected function formattedAmountSubtotal(): Attribute
    {
        return Attribute::make(
            get: fn () => Money::formatCurrency($this->amount_subtotal, $this->currency),
        );
    }

    protected function formattedAmountTotal(): Attribute
    {
        return Attribute::make(
            get: fn () => Money::formatCurrency($this->amount_total, $this->currency),
        );
    }

    protected function formattedAmountDiscount(): Attribute
    {
        return Attribute::make(
            get: fn () => Money::formatCurrency($this->amount_discount, $this->currency),
        );
    }

    protected function formattedAmountTax(): Attribute
    {
        return Attribute::make(
            get: fn () => Money::formatCurrency($this->amount_tax, $this->currency),
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
            get: fn () => $this->amount_total - $this->amount_refunded,
        );
    }

    protected function formattedAmountRefundable(): Attribute
    {
        return Attribute::make(
            get: fn () => Money::formatCurrency($this->amount_refundable, $this->currency),
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->associated instanceof PaidObject ?
                $this->associated->getPaymentLineDescriptor() : $value ?? 'Line Item',
        );
    }
}
