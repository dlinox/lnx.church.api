<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ZeroPaddingCast implements CastsAttributes
{
    protected $length;

    public function __construct($length = 5) // Cambia 5 por el número de ceros que necesites
    {
        $this->length = $length;
    }

    /**
     * Transforma el valor desde la base de datos.
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return str_pad($value, $this->length, '0', STR_PAD_LEFT);
    }

    /**
     * Transforma el valor antes de guardarlo en la base de datos.
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return ltrim($value, '0'); // Elimina los ceros para guardar el valor original
    }
}
