<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property User $author
 * @property string $message
 * @property bool $processed
 */
class Sms extends Model
{
    use HasFactory, BelongsToTenant, Searchable;

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

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'author' => $this->author->Forename.' '.$this->author->Surname,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'Tenant' => $this->Tenant,
        ];
    }
}
