<?php

namespace App\Models\Central;

use App\Models\Tenant\TenantOptions;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Tenant extends BaseTenant
{
    use HasDomains;

    protected $configOptionsCached = false;
    protected $configOptions = [];

    /**
     * Get the tenant id via expected attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['ID'],
        );
    }

    public function getOption($key)
    {
        if (!$this->configOptionsCached) {
            foreach ($this->tenantOptions as $option) {
                $this->configOptions[$option->Option] = $option->Value;
            }
            $this->configOptionsCached = true;
        }

        if (isset($this->configOptions[$key])) {
            return $this->configOptions[$key];
        }

        return null;
    }

    /**
     * Get the TenantOptions.
     */
    public function tenantOptions()
    {
        return $this->hasMany(TenantOptions::class, 'Tenant');
    }

    public static function getCustomColumns(): array
    {
        return [
            'ID',
            'Name',
            'Code',
            'Website',
            'Email',
            'Verified',
            'UniqueID',
            'Domain',
            'created_at',
            'updated_at',
            'Data'
        ];
    }

    public static function getDataColumn(): string
    {
        return 'Data';
    }

    public function getIncrementing()
    {
        return true;
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::retrieved(function ($model) {
            foreach ($model->tenantOptions as $option) {
                // $model->configOptions[] = $option;
                $model->setAttribute($option->Option, $option->Value);
                $model->syncOriginalAttribute($option->Option);
            }
            // ddd($model);
        });

        // static::retrieved(function ($tenant) {
        //     foreach ($tenant->tenantOptions as $option) {
        //         $tenant->configOptions[] = $option;
        //     }
        //     ddd($tenant);
        // });
    }
}
