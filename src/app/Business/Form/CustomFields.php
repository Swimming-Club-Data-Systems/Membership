<?php

namespace App\Business\Form;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class CustomFields
{
    public static function getValidationRules(?array $fields, string $prefix = null): array
    {
        $validationRules = [];

        if (Arr::isList($fields)) {
            foreach ($fields as $field) {
                $name = Arr::get($field, 'name');
                if ($name) {
                    $rules = [];
                    $type = Arr::get($field, 'type');
                    switch ($type) {
                        case 'checkbox':
                            $rules[] = 'boolean';
                            break;
                        case 'numeric':
                            $rules[] = 'numeric';
                            break;
                        case 'select':
                        case 'textbox':
                        case 'textarea':
                            $rules[] = 'string';
                            break;
                    }

                    if (Arr::get($field, 'required')) {
                        $rules[] = 'required';
                    } elseif ($type == 'textarea' || $type == 'textbox') {
                        $rules[] = 'nullable';
                    }

                    $items = Arr::get($field, 'items');
                    if (Arr::get($field, 'type') == 'select' && Arr::isList($items)) {
                        $collection = collect($items);
                        $values = $collection->map(function ($item) {
                            return Arr::get($item, 'value');
                        });
                        $rules[] = Rule::in($values);
                    }

                    $fieldName = $prefix ? $prefix.$name : $name;
                    $validationRules[$fieldName] = $rules;
                }
            }
        }

        return $validationRules;
    }

    public static function setValues($fields, $arrayObject, $values): void
    {
        if (Arr::isList($fields)) {
            foreach ($fields as $field) {
                if (Arr::get($field, 'name')) {
                    $name = Arr::get($field, 'name');

                    $arrayObject[$name] = $values[$name];
                }
            }
        }
    }
}
