<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $name
 * @property string $email
 * @property User $user
 * @property array $custom_form_data
 */
class CompetitionGuestEntryHeader extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'custom_form_data' => 'array',
    ];

    public function competitionGuestEntrants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CompetitionGuestEntrant::class);
    }

    /**
     * Get the user's name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['first_name'].' '.$attributes['last_name'],
        );
    }
}
