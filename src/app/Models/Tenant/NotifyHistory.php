<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Laravel\Scout\Searchable;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * @property int $ID
 * @property int $Sender
 * @property string $Subject
 * @property string $Message
 * @property bool $ForceSend
 * @property Date $Date
 * @property $JSONData
 * @property User $author
 * @property Date created_at
 * @property Date $updated_at
 */
class NotifyHistory extends Model
{
    use HasFactory, BelongsToTenant, Searchable;

    protected $primaryKey = 'ID';
    protected $table = 'notifyHistory';

    public function author()
    {
        return $this->belongsTo(User::class, 'Sender', 'UserID');
    }

    protected $casts = [
        'JSONData' => 'array',
    ];

    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        $fields = [
            'ID',
            'Subject',
            'Message',
            'Tenant',
        ];

        $data = array_intersect_key($array, array_flip($fields));

        $data['author'] = $this->author->Forename . ' ' . $this->author->Surname;

        return $data;
    }
}
