<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ID
 * @property bool $Website
 * @property bool $Social
 * @property bool $Noticeboard
 * @property bool $FilmTraining
 * @property bool $ProPhoto
 */
class MemberPhotography extends Model
{
    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Member::class, 'MemberID', 'MemberID');
    }

    protected $casts = [
        'Website' => 'boolean',
        'Social' => 'boolean',
        'Noticeboard' => 'boolean',
        'FilmTraining' => 'boolean',
        'ProPhoto' => 'boolean',
    ];

    protected $attributes = [
        'Website' => false,
        'Social' => false,
        'Noticeboard' => false,
        'FilmTraining' => false,
        'ProPhoto' => false,
    ];

    protected $fillable = [
        'Website',
        'Social',
        'Noticeboard',
        'FilmTraining',
        'ProPhoto',
    ];

    protected $primaryKey = 'ID';

    protected $table = 'memberPhotography';
}
