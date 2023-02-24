<?php

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property integer $id
 * @property ManualPaymentEntry $manualPaymentEntry
 * @property string $description
 * @property integer $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ManualPaymentEntryLine extends Model
{
    use HasFactory, BelongsToPrimaryModel;

    public function manualPaymentEntry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ManualPaymentEntry::class);
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'manualPaymentEntry';
    }
}
