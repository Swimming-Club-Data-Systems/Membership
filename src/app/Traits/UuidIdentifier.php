<?php

namespace App\Traits;

trait UuidIdentifier
{
    public $incrementing = false;
    protected $keyType = 'string';

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    protected static function bootUuidIdentifier()
    {
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) \Ramsey\Uuid\Uuid::uuid4();
            }
        });
    }
}
