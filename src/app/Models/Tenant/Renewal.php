<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property Carbon $start
 * @property Carbon $end
 * @property $default_stages
 * @property $default_member_stages
 * @property $metadata
 * @property MembershipYear $clubYear
 * @property MembershipYear $ngbYear
 * @property bool $started
 */
class Renewal extends Model
{
    use BelongsToTenant, HasUuids;

    protected $table = 'renewalv2';

    protected $casts = [
        'default_stages' => 'array',
        'default_member_stages' => 'array',
        'metadata' => AsArrayObject::class,
    ];

    public function onboardingSessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OnboardingSession::class, 'renewal', 'id');
    }

    public function ngbYear(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MembershipYear::class, 'ID', 'ngb_year');
    }

    public function clubYear(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MembershipYear::class, 'ID', 'club_year');
    }

    public function isCurrent(): bool
    {
        $today = new Carbon('now');

        return $this->start < $today && $today < $this->end;
    }

    public function isPast(): bool
    {
        $today = new Carbon('now');

        return $today > $this->end;
    }

    protected function started(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->isCurrent() || $this->isPast() || $this->onboardingSessions()->exists(),
        );
    }
}
