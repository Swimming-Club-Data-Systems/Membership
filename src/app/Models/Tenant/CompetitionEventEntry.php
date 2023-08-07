<?php

namespace App\Models\Tenant;

use App\Enums\CompetitionEntryCancellationReason;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property CompetitionEntry $entry
 * @property CompetitionEvent $event
 * @property float $entry_time
 * @property int $amount
 * @property int $amount_refunded
 * @property CompetitionEntryCancellationReason $cancellation_reason
 * @property string $notes
 */
class CompetitionEventEntry extends Model
{
    use HasFactory, HasUuids;

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
    ];

    public function entry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CompetitionEntry::class);
    }

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CompetitionEvent::class);
    }

    public function member(): Member
    {
        return $this->entry->member;
    }

    public function competitionGuestEntrant(): CompetitionGuestEntrant
    {
        return $this->entry->competitionGuestEntrant;
    }

    protected static function booted(): void
    {
        static::saving(function (CompetitionEventEntry $eventEntry) {
            if (! $eventEntry->exists && ! $eventEntry->isDirty('amount')) {
                /** @var CompetitionEvent $event */
                $event = $eventEntry->event()->first();
                $eventEntry->amount = $event->entry_fee + $event->processing_fee;
            }
        });
    }
}
