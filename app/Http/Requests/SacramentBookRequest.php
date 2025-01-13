<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SacramentBookRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->id ?? null;
        return [

            'number' => [
                'required',
                'integer',
                Rule::unique('sacrament_books', 'number')
                    ->where('sacrament_type', $this->sacrament_type)
                    ->ignore($id),
            ],

            'folios_number' => 'required|integer',
            'year_start' => 'required|date_format:Y',
            'year_finish' => 'nullable|date_format:Y',
            'acts_per_folio' => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'number.required' => 'El número de libro es requerido',
            'number.integer' => 'El número de libro debe ser un número entero',
            'number.unique' => 'El número de libro ya está en uso',
            'folios_number.required' => 'El número de folios es requerido',
            'folios_number.integer' => 'El número de folios debe ser un número entero',
            'year_start.required' => 'El año de inicio es requerido',
            'year_start.date_format' => 'El año de inicio debe ser un año válido',
            'year_finish.date_format' => 'El año de fin debe ser un año válido',
            'acts_per_folio.required' => 'El número de actas por folio es requerido',
            'acts_per_folio.integer' => 'El número de actas por folio debe ser un número entero',
            'sacrament_type.required' => 'El tipo de sacramento es requerido',
            'sacrament_type.in' => 'El tipo de sacramento no es válido',
        ];
    }

    public function all($keys = null): array
    {
        $attributes = parent::all($keys);
        return collect($attributes)->mapWithKeys(function ($value, $key) {
            return [
                Str::snake($key) => $value,
            ];
        })->toArray();
    }
}
