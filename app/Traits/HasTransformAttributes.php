<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
trait HasTransformAttributes
{

    /**
     * Sobrescribir el método getAttribute para convertir de snake_case a camelCase.
     */
    public function getAttribute($key)
    {
        // Convertir la clave de camelCase a snake_case
        $snakeKey = \Illuminate\Support\Str::snake($key);
        
        // Verificar si el atributo existe antes de intentar acceder a él
        if (!array_key_exists($snakeKey, $this->attributes) && !method_exists($this, $key)) {
            return null;
        }

        return parent::getAttribute($snakeKey);
    }

    /**
     * Sobrescribir el método setAttribute para convertir de camelCase a snake_case.
     */
    public function setAttribute($key, $value)
    {
        // Convertir la clave de camelCase a snake_case
        $snakeKey = \Illuminate\Support\Str::snake($key);

        // Llama al método padre para atributos no existentes
        if (!array_key_exists($snakeKey, $this->attributes) && !method_exists($this, $key)) {
            return parent::setAttribute($key, $value);
        }

        return parent::setAttribute($snakeKey, $value);
    }
    /**
     * Convertir las propiedades visibles al formato camelCase.
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        if (is_null($attributes)) {
            return [];
        }

        return collect($attributes)
            ->mapWithKeys(fn($value, $key) => [\Illuminate\Support\Str::camel($key) => $value])
            ->toArray();
    }
}
