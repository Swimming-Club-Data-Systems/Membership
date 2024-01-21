<?php

namespace App\Models\Tenant;

use App\Enums\Sex;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

/**
 * @property int MemberID
 * @property int UserID
 * @property bool Status
 * @property string AccessKey
 * @property string MForename
 * @property string MSurname
 * @property string $name,
 * @property string MMiddleNames
 * @property string ASANumber
 * @property \DateTime DateOfBirth
 * @property Sex Gender
 * @property string OtherNotes
 * @property bool ASAPrimary
 * @property bool ASAPaid
 * @property bool ClubPaid
 * @property string Country
 * @property bool Active
 * @property string GenderIdentity
 * @property string GenderPronouns
 * @property bool GenderDisplay
 * @property User user
 * @property MemberMedical|null $memberMedical
 * @property string|null $pronouns Pronouns if the member has chosen to display them
 */
class Member extends Model
{
    use BelongsToTenant, HasFactory, Searchable;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'DateOfBirth' => 'datetime',
        'Gender' => Sex::class,
    ];

    protected $attributes = [
        'OtherNotes' => '',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public function memberMedical()
    {
        return $this->hasOne(MemberMedical::class, 'MemberID');
    }

    public function squads(): BelongsToMany
    {
        return $this->belongsToMany(Squad::class, 'squadMembers', 'Member', 'Squad')
            ->withTimestamps()
            ->withPivot([
                'Paying',
            ]);
    }

    public function extraFees(): BelongsToMany
    {
        return $this->belongsToMany(ExtraFee::class, 'extrasRelations', 'MemberID', 'ExtraID')
            ->withTimestamps();
    }

    public function joiningSquads(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'squadMoves', 'Member', 'New')
            ->using(SquadMove::class);
    }

    public function leavingSquads(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'squadMoves', 'Member', 'Old')
            ->using(SquadMove::class);
    }

    public function squadMoves(): HasMany
    {
        return $this->hasMany(SquadMove::class, 'Member');
    }

    public function competitionEntries(): HasMany
    {
        return $this->hasMany(CompetitionEntry::class);
    }

    public function competitionEntryAvailableSessions(): BelongsToMany
    {
        return $this->belongsToMany(CompetitionSession::class)->withTimestamps();
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

    /**
     * Get the member name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['MForename'].' '.$attributes['MSurname'],
        );
    }

    /**
     * Get the member name.
     */
    protected function pronouns(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['GenderDisplay'] ? $attributes['GenderPronouns'] : null,
        );
    }

    /**
     * Get the member's age at the supplied date
     */
    public function ageAt(Carbon $date): int
    {
        $diff = $this->DateOfBirth->diff($date);

        return $diff->y;
    }

    /**
     * Get the member's age today
     */
    public function age(): int
    {
        return $this->ageAt(Carbon::now());
    }
}
