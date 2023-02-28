<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * @property int $id
 * @property User $user
 * @property boolean $posted
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ManualPaymentEntry extends Model
{
    use HasFactory, Prunable, BelongsToTenant;

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['user', 'users', 'lines'];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'posted' => false,
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ManualPaymentEntryLine::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('posted', false)->where('created_at', '<=', now()->subHours(3));
    }
}
