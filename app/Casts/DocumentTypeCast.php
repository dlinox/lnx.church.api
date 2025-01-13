<?php

namespace App\Casts;

use App\Constants\DocumentTypes;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class DocumentTypeCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return DocumentTypes::item($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return DocumentTypes::value($value['title']);
    }
}
