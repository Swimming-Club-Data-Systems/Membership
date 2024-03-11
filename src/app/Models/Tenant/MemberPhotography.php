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

    protected $primaryKey = 'ID';

    protected $table = 'memberPhotography';
}
