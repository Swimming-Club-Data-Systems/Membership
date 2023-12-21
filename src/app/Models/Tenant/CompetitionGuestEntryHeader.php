<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $name
 * @property string $email
 * @property User|null $user
 * @property array $custom_form_data
 * @property Collection $competitionGuestEntrants
 * @property bool $complete
 */
class CompetitionGuestEntryHeader extends Model
{
    use BelongsToTenant, HasUuids;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'custom_form_data' => AsArrayObject::class,
        'complete' => 'boolean',
    ];

    protected $attributes = [
        'custom_form_data' => '{}',
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
            get: fn (mixed $value, array $attributes) => $this->first_name.' '.$this->last_name,
        );
    }

    /**
     * Get the user's email.
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->user ? $this->user->email : $attributes['email'],
        );
    }

    /**
     * Get the user's first name.
     */
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->user ? $this->user->Forename : $attributes['first_name'],
        );
    }

    /**
     * Get the user's last name.
     */
    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->user ? $this->user->Surname : $attributes['last_name'],
        );
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
