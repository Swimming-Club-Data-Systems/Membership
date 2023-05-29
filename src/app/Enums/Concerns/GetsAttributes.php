<?php

namespace App\Enums\Concerns;

use App\Enums\Attributes\Description;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Str;
use ReflectionClassConstant;

trait GetsAttributes
{
    /**
     * @param self $enum
     */
    public static function getDescription(self $enum): string
    {
        $ref = new ReflectionClassConstant(self::class, $enum->name);
        $classAttributes = $ref->getAttributes(Description::class);

        if (count($classAttributes) === 0) {
            return Str::headline($enum->value);
        }

        return $classAttributes[0]->newInstance()->description;
    }
}
