<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Central\Tenant;

/**
 * @property int $ID
 * @property string $Option
 * @property string $Value
 */
class TenantOptions extends Model
{
    use HasFactory;

    /**
     * Get the tenant that owns this option.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'Tenant', 'ID');
    }

    protected $table = 'tenantOptions';
}
