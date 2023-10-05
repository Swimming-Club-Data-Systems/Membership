<?php

namespace App\Models\Tenant;

use App\Enums\Sex;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $name
 * @property Carbon $date_of_birth
 * @property Sex $sex
 * @property int $age
 * @property CompetitionGuestEntryHeader $competitionGuestEntryHeader
 * @property array-key|null $custom_form_data
 */
class CompetitionGuestEntrant extends Model
{
    use HasFactory, HasUuids, BelongsToPrimaryModel;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'custom_form_data' => AsArrayObject::class,
        'date_of_birth' => 'datetime',
        'sex' => Sex::class,
    ];

    protected $attributes = [
        'custom_form_data' => '{}',
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

    /**
     * Get the entrant's name.
     */
    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->ageAt(Carbon::now()),
        );
    }

    /**
     * Get the member's age at the supplied date
     */
    public function ageAt(Carbon $date): int
    {
        $diff = $this->date_of_birth->diff($date);

        return $diff->y;
    }

    public function getCustomFieldData(Competition $competition)
    {
        $fields = Arr::get($competition->custom_fields, 'guest_entrant_fields', []);

        $data = [];
        foreach ($fields as $field) {
            if (Arr::get($field, 'name')) {
                // Try and find in guest entrant data
                $fieldName = Arr::get($field, 'name');
                $fieldValue = property_exists($this->custom_form_data, $fieldName) ? $this->custom_form_data->$fieldName : null;
                $fieldFriendlyName = Arr::get($field, 'label') ?? Arr::get($field, 'name');

                $friendlyValue = $fieldValue;

                if (Arr::get($field, 'type') === 'select') {
                    try {
                        // Find the value label
                        foreach (Arr::get($field, 'items') as $item) {
                            if (Arr::get($item, 'value') === $fieldValue) {
                                $friendlyValue = Arr::get($item, 'name');
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        report($e);
                        // Ignore
                    }
                }

                $data[] = [
                    'name' => Arr::get($field, 'name'),
                    'friendly_name' => $fieldFriendlyName,
                    'value' => $fieldValue,
                    'friendly_value' => $friendlyValue,
                ];
            }
        }

        return $data;
    }
}
