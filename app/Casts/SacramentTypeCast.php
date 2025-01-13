<?php

namespace App\Casts;

use App\Constants\Sacraments;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SacramentTypeCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return Sacraments::item($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return Sacraments::value($value['title']);
    }
}
