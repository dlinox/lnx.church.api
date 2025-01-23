<?php

namespace App\Casts;

use App\Constants\DocumentTypes;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class DocumentTypeCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }
        return DocumentTypes::item($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }
        //si es string y esta en el enum
        if (is_string($value) && DocumentTypes::hasKey($value)) {
            return $value;
        }
        return DocumentTypes::value($value['title']);
    }
}
