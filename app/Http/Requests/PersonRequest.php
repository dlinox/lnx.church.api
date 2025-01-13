<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PersonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->id ?? null;
        return [
            'document_number' => [
                'max:15',
                Rule::unique('people', 'document_number')
                    ->where('document_type', $this->document_type)
                    ->ignore($id),
            ],

            'name' => 'required|string|max:80',
        ];
    }

    public function messages(): array
    {
        return [
            'document_number.required' => 'El número de documento es obligatorio.',
            'document_number.max' => 'El número de documento no puede superar los 15 caracteres.',
            'document_number.unique' => 'El número de documento ya está en uso.',
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede superar los 80 caracteres.',
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
