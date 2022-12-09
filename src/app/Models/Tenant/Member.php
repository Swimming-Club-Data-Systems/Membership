<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * @property int MemberID
 * @property int UserID
 * @property boolean Status
 * @property string AccessKey
 * @property string MForename
 * @property string MSurname
 * @property string $name,
 * @property string MMiddleNames
 * @property string ASANumber
 * @property \DateTime DateOfBirth
 * @property string Gender
 * @property string OtherNotes
 * @property boolean ASAPrimary
 * @property boolean ASAPaid
 * @property boolean ClubPaid
 * @property string Country
 * @property boolean Active
 * @property string GenderIdentity
 * @property string GenderPronouns
 * @property boolean GenderDisplay
 * @property User user
 */
class Member extends Model
{
    use HasFactory, BelongsToTenant, Searchable;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'DateOfBirth' => 'datetime',
    ];
    protected $primaryKey = 'MemberID';

    /**
     * Determine if the model should be searchable.
     *
     * @return bool
     */
    public function shouldBeSearchable()
    {
        return $this->Active;
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'UserID', 'UserID');
    }

    public function squads(): BelongsToMany
    {
        return $this->belongsToMany(Squad::class, 'squadMembers', 'Member', 'Squad')
            ->withTimestamps()
            ->withPivot([
                'Paying'
            ]);
    }

    /**
     * Get the member name.
     *
     * @return Attribute
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['MForename'] . ' ' . $attributes['MSurname'],
        );
    }

    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        $fields = [
            'MemberID',
            'MForename',
            'MSurname',
            'MMiddleNames',
            'ASANumber',
            'DateOfBirth',
            'Tenant',
        ];

        return array_intersect_key($array, array_flip($fields));
    }

}
