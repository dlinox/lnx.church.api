<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class JsonObjectCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return json_decode($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {

        if (is_array($value)) {
            return $value['value'];
        }
        return $value ? $value->value : null;
    }
}
