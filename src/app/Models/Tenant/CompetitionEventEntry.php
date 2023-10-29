<?php

namespace App\Models\Tenant;

use App\Enums\CompetitionEntryCancellationReason;
use App\Interfaces\PaidObject;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property CompetitionEntry $competitionEntry
 * @property CompetitionEvent $competitionEvent
 * @property float $entry_time
 * @property int $amount
 * @property int $amount_refunded
 * @property string $amount_string
 * @property string $amount_refunded_string
 * @property CompetitionEntryCancellationReason $cancellation_reason
 * @property string $notes
 * @property bool $paid
 * @property PaymentLine|null $paymentLine
 * @property bool $refunded
 * @property bool $fully_refunded
 */
class CompetitionEventEntry extends Model implements PaidObject
{
    use HasUuids;

    protected $fillable = [
        'competition_entry_id',
        'competition_event_id',
        'entry_time',
        'amount',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'cancellation_reason' => CompetitionEntryCancellationReason::class,
        'paid' => 'boolean',
        'refunded' => 'boolean',
        'fully_refunded' => 'boolean',
    ];

    protected $attributes = [
        'paid' => false,
    ];

    public function competitionEntry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CompetitionEntry::class);
    }

    public function competitionEvent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CompetitionEvent::class);
    }

    public function member(): ?Member
    {
        return $this->competitionEntry->member;
    }

    public function competitionGuestEntrant(): ?CompetitionGuestEntrant
    {
        return $this->competitionEntry->competitionGuestEntrant;
    }

    public function paymentLine(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(PaymentLine::class, 'associated_uuid');
    }

    public function populateFromEvent(CompetitionEvent $event): void
    {
        $this->amount = $event->entry_fee + $event->processing_fee;
    }

    /**
     * Get or set the amount as a string.
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

    /**
     * Get or set the amount refunded as a string.
     */
    protected function amountRefundedString(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => (string) BigDecimal::of((string) $attributes['amount_refunded'])->withPointMovedLeft(2),
            set: fn ($value, $attributes) => [
                'amount_refunded' => BigDecimal::of($value)->withPointMovedRight(2)->toInt(),
            ],
        );
    }

    /**
     * Get refunded status.
     */
    protected function refunded(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['amount_refunded'] > 0,
        );
    }

    /**
     * Get fully refunded status.
     */
    protected function fullyRefunded(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['amount_refunded'] == $attributes['amount'],
        );
    }

    public function handlePaid($line): void
    {
        $this->paid = true;
        $this->save();

        $this->competitionEntry->paid = true;
        $this->competitionEntry->save();

        // Credit the competition journal
        // Get competition
        $competition = $this->competitionEvent->competition;
        $competition->journal->credit($line->amount_total);

        if ($this->competitionEntry->competitionGuestEntrant) {
            $header = $this->competitionEntry->competitionGuestEntrant->competitionGuestEntryHeader;
            if (! $header->complete) {
                $header->complete = true;
                $header->save();
            }
        }
    }

    public function handleChargedBack(): void
    {
        // TODO: Implement handleChargedBack() method.
    }

    public function getPaymentLineDescriptor(): string
    {
        $name = $this->member() ? $this->member()->name : $this->competitionGuestEntrant()->name;

        return $name.' - '.$this->competitionEvent->name;
    }

    public function handleRefund(int $refundAmount, int $totalAmountRefunded): void
    {
        $this->amount_refunded = $totalAmountRefunded;

        if (! $this->cancellation_reason) {
            $this->cancellation_reason = CompetitionEntryCancellationReason::GENERIC_REFUND;
        }

        $this->save();

        $this->competitionEntry->calculateTotals();
        $this->competitionEntry->save();

        // Debit the competition journal
        // Get competition
        $competition = $this->competitionEvent->competition;
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
}
