<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Squad extends Model
{
    use HasFactory;

    protected $primaryKey = 'SquadID';

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'squadMembers', 'Squad', 'Member')
            ->withTimestamps()
            ->withPivot([
                'Paying'
            ]);
    }
}
