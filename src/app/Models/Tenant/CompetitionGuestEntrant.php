<?php

namespace App\Models\Tenant;

use App\Enums\Sex;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $name
 * @property Carbon $date_of_birth
 * @property Sex $sex
 * @property CompetitionGuestEntryHeader $competition_guest_entry_header
 */
class CompetitionGuestEntrant extends Model
{
    use HasFactory, HasUuids, BelongsToPrimaryModel;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'custom_form_data' => 'array',
        'date_of_birth' => 'datetime',
        'sex' => Sex::class,
    ];

    protected $attributes = [
        'custom_form_data' => '[]',
    ];

    public function competitionGuestEntryHeader(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CompetitionGuestEntryHeader::class);
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'competitionGuestEntryHeader';
    }

    /**
     * Get the entrant's name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['first_name'].' '.$attributes['last_name'],
        );
    }
}
