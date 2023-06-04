<?php

namespace App\Models\Tenant;

use App\Models\Central\Tenant;
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
 * @property Tenant $tenant
 */
class NotifyHistory extends Model
{
    use HasFactory, BelongsToTenant, Searchable;

    protected $primaryKey = 'ID';

    protected $table = 'notifyHistory';

    private $_attachments;

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
            'Date',
            'Tenant',
        ];

        $data = array_intersect_key($array, array_flip($fields));

        $data['author'] = $this->author ? $this->author->Forename.' '.$this->author->Surname : null;

        return $data;
    }

    public function attachments(): array
    {
        if (! $this->_attachments) {
            $this->_attachments = [];
            if (isset($this->JSONData['Attachments'])) {
                foreach ($this->JSONData['Attachments'] as $key => $data) {
                    $this->_attachments[] = [
                        'key' => $key,
                        'name' => $data['Filename'],
                        'mime_type' => $data['MIME'],
                        'path' => $data['URI'],
                        's3_path' => $this->tenant->ID.'/'.$data['URI'],
                    ];
                }
            }
        }

        return $this->_attachments ?? [];
    }

    public function fromName(): string
    {
        if (isset($this->JSONData['NamedSender'])) {
            return $this->JSONData['NamedSender']['Name'];
        }

        return $this->tenant->Name;
    }

    public function replyToName(): string
    {
        if (isset($this->JSONData['ReplyToMe'])) {
            return $this->JSONData['ReplyToMe']['Name'];
        }

        return $this->tenant->Name;
    }

    public function replyToEmail(): string
    {
        if (isset($this->JSONData['ReplyToMe'])) {
            return $this->JSONData['ReplyToMe']['Email'];
        }

        return $this->tenant->Email;
    }
}
