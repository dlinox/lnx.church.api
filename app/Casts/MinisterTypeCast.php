<?php

namespace App\Casts;

use App\Constants\MinisterTypes;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class MinisterTypeCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return MinisterTypes::item($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return MinisterTypes::value($value['title']);
    }
}
