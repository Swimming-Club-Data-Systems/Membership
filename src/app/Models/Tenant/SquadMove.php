<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

/**
 * @property Date Date
 * @property Squad oldSquad
 * @property Squad newSquad
 * @property bool Paying
 * @property Member member
 */
class SquadMove extends Model
{
    // The squadsMoves table has an ID column
    // public $incrementing = true;

    protected $casts = [
        'Date' => 'datetime',
    ];

    public function oldSquad(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Squad::class, 'Old', 'SquadID');
    }

    public function newSquad(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Squad::class, 'New', 'SquadID');
    }

    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Member::class, 'Member', 'MemberID');
    }

    protected $table = 'squadMoves';

    protected $primaryKey = 'ID';
}
