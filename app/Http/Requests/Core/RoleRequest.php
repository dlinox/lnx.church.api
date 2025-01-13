<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->id ?? null;
        return [
            'display_name' => 'required|max:255|unique:roles,display_name,' . $id,
            'name' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'display_name.unique' => 'El nombre ya está en uso',
            'display_name.required' => 'El campo nombre es obligatorio',
            'displayName.max' => 'El campo nombre no debe ser mayor a 255 caracteres',
        ];
    }

    public function all($keys = null)
    {
        $attributes = parent::all($keys);
        return collect($attributes)->mapWithKeys(function ($value, $key) {
            return [
                ...($key == 'displayName' ? ['name' => Str::snake($value)] : []),
                Str::snake($key) => $value,
            ];
        })->toArray();
    }
}
