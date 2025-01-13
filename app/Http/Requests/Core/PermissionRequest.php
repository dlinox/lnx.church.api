<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class PermissionRequest extends FormRequest
{
    public function all($keys = null)
    {
        // Obtener los datos originales del request
        $attributes = parent::all($keys);

        // Convertir las claves de camelCase a snake_case
        return collect($attributes)->mapWithKeys(function ($value, $key) {
            return [
                Str::snake($key) => $value,
                //add name attribute
                ...($key == 'displayName' ? ['name' => Str::snake($value)] : []),
            ];
        })->toArray();
    }
}
