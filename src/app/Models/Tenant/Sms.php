<?php

namespace App\Models\Tenant;

use App\Business\Helpers\Money;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property User $author
 * @property string $message
 * @property bool $processed
 * @property string $currency
 * @property int $amount
 * @property int $number_sent
 * @property int $segments_sent
 * @property string $formatted_amount
 */
class Sms extends Model
{
    use BelongsToTenant, Searchable;

    /**
     * @var array
     */
    protected $attributes = [
        'processed' => false,
        'message' => '',
        'currency' => 'gbp',
        'amount' => 0,
        'number_sent' => 0,
        'segments_sent' => 0,
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'message' => 'string',
        'processed' => 'boolean',
        'currency' => 'string',
        'amount' => 'integer',
        'number_sent' => 'integer',
        'segments_sent' => 'integer',
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'message',
        'processed',
        'currency',
        'amount',
        'number_sent',
        'segments_sent',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['formatted_amount'];

    public function squads(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(Squad::class, 'sms_groupable');
    }

    public function recipients(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(User::class, 'smsable');
    }

    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => Money::formatCurrency($this->amount, $this->currency),
        );
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
