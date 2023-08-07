<?php

namespace App\Models\Tenant;

use App\Business\Helpers\Money;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property Member $member
 * @property CompetitionGuestEntrant $competitionGuestEntrant
 * @property bool $paid
 * @property bool $processed
 * @property int $amount
 * @property int $amount_refunded
 * @property string $formatted_amount
 * @property string $formatted_amount_refunded
 * @property string $amount_string
 * @property string $amount_refunded_string
 * @property bool $refundable
 * @property bool $approved
 * @property bool $locked
 * @property Collection $events
 */
class CompetitionEntry extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'competition_id',
        'competition_guest_entrant_id',
        'member_MemberID',
    ];

    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function guest(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CompetitionGuestEntrant::class);
    }

    public function events(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CompetitionEventEntry::class);
    }

    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => Money::formatCurrency($this->amount),
        );
    }

    protected function formattedAmountRefunded(): Attribute
    {
        return Attribute::make(
            get: fn () => Money::formatCurrency($this->amount_refunded),
        );
    }

    /**
     * Get or set the amount as a string.
     */
    protected function amountString(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => (string) BigDecimal::of((string) $attributes['amount'])->withPointMovedLeft(2),
            set: fn ($value) => [
                'amount' => BigDecimal::of($value)->withPointMovedRight(2)->toInt(),
            ],
        );
    }

    /**
     * Get or set the amount refunded as a string.
     */
    protected function amountRefundedString(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => (string) BigDecimal::of((string) $attributes['amount_refunded'])->withPointMovedLeft(2),
            set: fn ($value) => [
                'amount_refunded' => BigDecimal::of($value)->withPointMovedRight(2)->toInt(),
            ],
        );
    }

    /**
     * Calculate the totals for the associated event entries. Does not save the model.
     */
    public function calculateTotals(): void
    {
        $this->amount = $this->events()->sum('amount');
        $this->amount_refunded = $this->events()->sum('amount_refunded');
    }
}
