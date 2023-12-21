<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $path
 * @property string $disk
 * @property string $original_name
 * @property string $public_name
 * @property string $mime_type
 * @property int $size
 * @property bool $public
 * @property int $sequence
 */
class CompetitionFile extends Model
{
    use HasUuids;

    protected $fillable = [
        'path',
        'disk',
        'original_name',
        'public_name',
        'mime_type',
        'size',
        'public',
        'sequence',
    ];

    protected $attributes = [
        'public' => false,
    ];

    protected $casts = [
        'public' => 'boolean',
        'sequence' => 'integer',
    ];

    public function competition(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }
}
