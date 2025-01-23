<?php

namespace App\Casts;

use App\Constants\Genders;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class GenderCast implements CastsAttributes
{

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return Genders::item($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) return '0';

        //si es string y esta en el enum
        if (is_string($value) && Genders::hasKey($value)) {
            return $value;
        }
        return Genders::value($value['title']);
    }
}
