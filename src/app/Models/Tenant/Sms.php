<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

/**
 * @property int $id
 * @property User $author
 * @property string $message
 * @property boolean $processed
 */
class Sms extends Model
{
    use HasFactory, BelongsToTenant;

    public function squads()
    {
        return $this->morphedByMany(Squad::class, 'sms_groupable');
    }

    public function recipients()
    {
        return $this->morphedByMany(User::class, 'smsable');
    }

    public function author()
    {
        return $this->belongsTo(User::class);
    }
}
