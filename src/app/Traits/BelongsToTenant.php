<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Database\TenantScope;

/**
 * @property-read Tenant $tenant
 *
 * This trait exists because of a PHP Error thrown by the normal one
 */
trait BelongsToTenant
{
    public static $tenantIdColumn = 'Tenant';

    public static function bootBelongsToTenant()
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model) {
            if (!$model->getAttributeValue('Tenant') && !$model->relationLoaded('tenant')) {
                if (tenancy()->initialized) {
                    $model->setAttribute('Tenant', tenant()->getTenantKey());
                    $model->setRelation('tenant', tenant());
                }
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(config('tenancy.tenant_model'), BelongsToTenant::$tenantIdColumn);
    }
}
