<?php

namespace App\Models\Tenant;

use App\Enums\CompetitionCategory;
use App\Enums\CompetitionCourse;
use App\Enums\CompetitionMode;
use App\Enums\CompetitionStatus;
use App\Enums\DistanceUnits;
use App\Enums\EventCode;
use App\Enums\Stroke;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo(CompetitionSession::class);
    }

    public function competition(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(Competition::class, CompetitionSession::class);
    }

    /**
     * Get or set the default entry fee amount as a string.
     *
     * @return Attribute
     */
    protected function entryFeeString(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => (string)BigDecimal::of((string)$attributes['entry_fee'])->withPointMovedLeft(2),
            set: fn($value, $attributes) => [
                'entry_fee' => BigDecimal::of($value)->withPointMovedRight(2)->toInt()
            ],
        );
    }

    /**
     * Get or set the processing fee amount as a string.
     *
     * @return Attribute
     */
    protected function processingFeeString(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => (string)BigDecimal::of((string)$attributes['processing_fee'])->withPointMovedLeft(2),
            set: fn($value, $attributes) => [
                'processing_fee' => BigDecimal::of($value)->withPointMovedRight(2)->toInt()
            ],
        );
    }
}
