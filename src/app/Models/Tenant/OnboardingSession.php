<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property int $id
 * @property int $user
 * @property \DateTime $created
 * @property int $creator
 * @property Date $start
 * @property int $charge_outstanding
 * @property int $charge_pro_rata
 * @property string $welcome_text
 * @property string $token
 * @property string $token_on
 * @property string $status
 * @property Date $due_date
 * @property \DateTime $completed_at
 */
class OnboardingSession extends Model
{
    use HasFactory, BelongsToPrimaryModel;

    protected $table = 'onboardingSessions';

    public function getRelationshipToPrimaryModel(): string
    {
        return 'user';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user', 'UserID');
    }
}
