<?php

namespace App\Models\Tenant;

use App\Enums\CompetitionCourse;
use App\Enums\CompetitionMode;
use App\Enums\CompetitionOpenTo;
use App\Enums\CompetitionStatus;
use App\Events\Tenant\CompetitionCreated;
use App\Traits\BelongsToTenant;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property CompetitionCourse $pool_course
 * @property Venue $venue
 * @property CompetitionMode $mode
 * @property CompetitionStatus $status
 * @property CompetitionOpenTo $open_to
 * @property bool $require_times
 * @property bool $coach_enters
 * @property bool $requires_approval
 * @property bool $public
 * @property int $processing_fee
 * @property int $default_entry_fee
 * @property string $processing_fee_string
 * @property string $default_entry_fee_string
 * @property Carbon $closing_date
 * @property Carbon $gala_date
 * @property Carbon $age_at_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Collection $events
 * @property $custom_fields
 */
class Competition extends Model
{
    use HasFactory, Searchable, BelongsToTenant;

    protected $fillable = [
        'name',
        'description',
        'pool_course',
        'require_times',
        'coach_enters',
        'requires_approval',
        'public',
        'default_entry_fee_string',
        'processing_fee_string',
        'closing_date',
        'age_at_date',
        'status',
        'open_to',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'pool_course' => CompetitionCourse::class,
        'status' => CompetitionStatus::class,
        'mode' => CompetitionMode::class,
        'open_to' => CompetitionOpenTo::class,
        'closing_date' => 'datetime',
        'gala_date' => 'datetime',
        'age_at_date' => 'datetime',
        'require_times' => 'boolean',
        'coach_enters' => 'boolean',
        'requires_approval' => 'boolean',
        'public' => 'boolean',
        'custom_fields' => 'object',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'pool_course' => CompetitionCourse::SHORT,
        'mode' => CompetitionMode::BASIC,
        'status' => CompetitionStatus::DRAFT,
        'open_to' => CompetitionOpenTo::MEMBERS,
        'require_times' => false,
        'coach_enters' => false,
        'requires_approval' => false,
        'public' => true,
        'default_entry_fee' => 0,
        'processing_fee' => 0,
        'description' => '',
        'custom_fields' => '{}',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['venue', 'sessions'];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => CompetitionCreated::class,
    ];

    public function venue(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function sessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CompetitionSession::class)->orderBy('sequence', 'asc');
    }

    public function events(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(CompetitionEvent::class, CompetitionSession::class);
    }

    /**
     * Get or set the default entry fee amount as a string.
     */
    protected function defaultEntryFeeString(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => (string) BigDecimal::of((string) $attributes['default_entry_fee'])->withPointMovedLeft(2),
            set: fn ($value, $attributes) => [
                'default_entry_fee' => BigDecimal::of($value)->withPointMovedRight(2)->toInt(),
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
}
