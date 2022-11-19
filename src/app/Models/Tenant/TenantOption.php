<?php

namespace App\Models\Tenant;

use App\Models\Central\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ID
 * @property string $Option
 * @property string $Value
 * @property Tenant $tenant
 */
class TenantOption extends Model
{
    use HasFactory;

    protected $table = 'tenantOptions';

    /**
     * Get the tenant that owns this option.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'Tenant', 'ID');
    }
}
