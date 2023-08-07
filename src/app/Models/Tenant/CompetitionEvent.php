<?php

namespace App\Models\Tenant;

use App\Enums\CompetitionCategory;
use App\Enums\DistanceUnits;
use App\Enums\EventCode;
use App\Enums\Sex;
use App\Enums\Stroke;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property int $sequence
 * @property Stroke $stroke
 * @property DistanceUnits $units
 * @property int $distance
 * @property EventCode $event_code
 * @property array $ages
 * @property int $entry_fee
 * @property string $entry_fee_string
 * @property CompetitionCategory $category
 * @property int $processing_fee
 * @property string $processing_fee_string
 * @property Competition $competition
 */
class CompetitionEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'ages' => 'array',
        'event_code' => EventCode::class,
        'stroke' => Stroke::class,
        'units' => DistanceUnits::class,
        'category' => CompetitionCategory::class,
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'units' => DistanceUnits::METRES,
        'ages' => '["OPEN"]',
        'entry_fee' => 0,
        'processing_fee' => 0,
    ];

    /**
     * The model's fillable attributes.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'stroke',
        'distance',
        'event_code',
        'sequence',
        'entry_fee',
        'category',
        'units',
        'ages',
        'entry_fee',
        'entry_fee_string',
        'processing_fee',
        'processing_fee_string',
    ];

    public function session(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CompetitionSession::class, 'competition_session_id');
    }

    public function competition(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(Competition::class, CompetitionSession::class);
    }

    public function eventEntries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CompetitionEventEntry::class);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stroke' => $this->stroke,
            'units' => $this->units,
            'distance' => $this->distance,
            'event_code' => $this->event_code,
            'sequence' => $this->sequence,
            'ages' => $this->ages,
            'entry_fee' => $this->entry_fee,
            'entry_fee_string' => $this->entry_fee_string,
            'processing_fee' => $this->processing_fee,
            'processing_fee_string' => $this->processing_fee_string,
            'category' => $this->category,
            'competition_session_id' => $this->competition_session_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get or set the default entry fee amount as a string.
     */
    protected function entryFeeString(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => (string) BigDecimal::of((string) $attributes['entry_fee'])->withPointMovedLeft(2),
            set: fn ($value, $attributes) => [
                'entry_fee' => BigDecimal::of($value)->withPointMovedRight(2)->toInt(),
            ],
        );
    }

    /**
     * Get or set the processing fee amount as a string.
     */
    protected function processingFeeString(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => (string) BigDecimal::of((string) $attributes['processing_fee'])->withPointMovedLeft(2),
            set: fn ($value, $attributes) => [
                'processing_fee' => BigDecimal::of($value)->withPointMovedRight(2)->toInt(),
            ],
        );
    }

    public function categoryMatches(Sex $sex): bool
    {
        if ($sex == Sex::FEMALE) {
            return in_array($this->category, [
                CompetitionCategory::FEMALE,
                CompetitionCategory::GIRL,
                CompetitionCategory::MIXED,
            ]);
        } else {
            return in_array($this->category, [
                CompetitionCategory::MALE,
                CompetitionCategory::OPEN,
                CompetitionCategory::BOY,
                CompetitionCategory::MIXED,
            ]);
        }
    }

    public function ageMatches(int $age)
    {
        for ($i = 0; $i < count($this->ages); $i++) {
            if ($this->ages[$i] == 'OPEN') {
                return true;
            } elseif (Str::startsWith($this->ages[$i], '-')) {
                // Up to age $this->ages[$i]
                $ages = Str::of($this->ages[$i])->explode('-');

                if ($age <= $ages[0]) {
                    return true;
                }
            } elseif (Str::endsWith($this->ages[$i], '-')) {
                // Age $this->ages[$i] and up
                $ages = Str::of($this->ages[$i])->explode('-');

                if ($age >= $ages[0]) {
                    return true;
                }
            } elseif (Str::contains($this->ages[$i], '-')) {
                // Ages between [0] and [1]
                $ages = Str::of($this->ages[$i])->explode('-');

                if ($age >= $ages[0] && $age <= $ages[0]) {
                    return true;
                }
            } else {
                // Single age group
                if ($age == $this->ages[$i]) {
                    return true;
                }
            }
        }

        return false;
    }
}
