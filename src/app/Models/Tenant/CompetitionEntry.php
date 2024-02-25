<?php

namespace App\Models\Tenant;

use App\Business\Helpers\Money;
use App\Interfaces\PaidObject;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property ?Member $member
 * @property ?CompetitionGuestEntrant $competitionGuestEntrant
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
 * @property bool $processing_fee_paid
 * @property bool $vetoable
 * @property Collection $competitionEventEntries
 * @property Competition $competition
 * @property bool $editable
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CompetitionEntry extends Model implements PaidObject
{
    use HasUuids;

    protected $fillable = [
        'competition_id',
        'competition_guest_entrant_id',
        'member_MemberID',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'paid' => 'boolean',
        'processed' => 'boolean',
        'approved' => 'boolean',
        'locked' => 'boolean',
        'refundable' => 'boolean',
        'vetoable' => 'boolean',
        'processing_fee_paid' => 'boolean',
    ];

    protected $attributes = [
        'paid' => false,
        'processing_fee_paid' => false,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['editable', 'amount_formatted', 'amount_refunded_formatted'];

    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function competition(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function competitionGuestEntrant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CompetitionGuestEntrant::class);
    }

    public function competitionEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CompetitionEvent::class, CompetitionEventEntry::class);
    }

    public function competitionEventEntries()
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
        );
    }

    /**
     * Get or set the amount as a string.
     */
    protected function amountFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => Money::formatCurrency((string) $attributes['amount']),
        );
    }

    /**
     * Get or set the amount refunded as a string.
     */
    protected function amountRefundedFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => Money::formatCurrency($attributes['amount_refunded']),
        );
    }

    /**
     * Calculate the totals for the associated event entries.
     * Does not include competition processing fees.
     * Does not save the model.
     */
    public function calculateTotals(): void
    {
        $this->amount = $this->competitionEventEntries()->sum('amount');
        $this->amount_refunded = $this->competitionEventEntries()->sum('amount_refunded');
    }

    public function handlePaid($line): void
    {
        $this->processing_fee_paid = true;

        // Credit the competition journal
        // Get competition
        $competition = $this->competition;
        $competition->journal->credit($line->amount_total);
    }

    public function handleChargedBack(): void
    {
        // TODO: Implement handleChargedBack() method.
    }

    public function getPaymentLineDescriptor(): string
    {
        // TODO: Implement getPaymentLineDescriptor() method.

        return 'Processing fee for competition entry '.$this->id;
    }

    public function handleRefund(int $refundAmount, int $totalAmountRefunded): void
    {
        // TODO: Implement handleRefund() method.

        // Debit the competition journal
        // Get competition
        $competition = $this->competition;
        $competition->journal->debit($refundAmount);
    }

    public function handleFailed(): void
    {
        // TODO: Implement handleFailed() method.
    }

    public function handleCanceled(): void
    {
        // TODO: Implement handleCanceled() method.
    }

    /**
     * Get the user's first name.
     */
    protected function editable(): Attribute
    {
        return Attribute::make(
            get: fn () => ! $this->paid && ! $this->processed,
        );
    }
}
