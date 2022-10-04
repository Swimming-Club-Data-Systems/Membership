<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * @property int SquadID
 * @property string SquadName
 * @property float SquadFee
 * @property string SquadCoach
 * @property string SquadTimetable
 * @property string SquadCoC
 * @property string SquadKey
 */
class Squad extends Model
{
    use HasFactory, BelongsToTenant;

    protected $primaryKey = 'SquadID';

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'squadMembers', 'Squad', 'Member')
            ->withTimestamps()
            ->withPivot([
                'Paying'
            ]);
    }

    public function reps(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'squadReps', 'Squad', 'User')
            ->withTimestamps()
            ->withPivot([
                'ContactDescription'
            ]);
    }
}
