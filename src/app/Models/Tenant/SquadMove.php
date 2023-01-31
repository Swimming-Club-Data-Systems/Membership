<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Date;

/**
 * @property Date Date
 * @property Squad oldSquad
 * @property Squad newSquad
 * @property bool Paying
 * @property Member member
 */
class SquadMove extends Pivot
{
    // The squadsMoves table has an ID column
    public $incrementing = true;

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
