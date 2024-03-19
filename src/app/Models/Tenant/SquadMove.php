<?php

namespace App\Models\Tenant;

use App\Events\Tenant\SquadMoveCreated;
use App\Events\Tenant\SquadMoveDeleted;
use App\Events\Tenant\SquadMoveUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Date;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property Date Date
 * @property Squad oldSquad
 * @property Squad newSquad
 * @property bool Paying
 * @property Member member
 */
class SquadMove extends Model
{
    use BelongsToPrimaryModel, Notifiable;

    // The squadsMoves table has an ID column
    // public $incrementing = true;

    protected $casts = [
        'Date' => 'datetime',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => SquadMoveCreated::class,
        'updated' => SquadMoveUpdated::class,
        'deleted' => SquadMoveDeleted::class,
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

    public function getRelationshipToPrimaryModel(): string
    {
        return 'member';
    }

    public function handleMove()
    {
        if ($this->oldSquad) {
            $this->member->squads()->detach($this->oldSquad);
        }

        if ($this->newSquad) {
            $this->member->squads()->attach($this->newSquad, [
                'Paying' => $this->Paying,
            ]);
        }

        // Do not trigger events
        $this->deleteQuietly();
    }
}
