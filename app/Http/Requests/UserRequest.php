<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $id = $this->id ? ',' . $this->id : '';

        return [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email' . $id,
            'username' => 'required|max:255|unique:users,username' . $id,
            'password' => 'nullable|min:6',
            'status' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Obligatorio',
            'name.max' => 'Maximo 255 caracteres',
            'email.required' => 'Obligatorio',
            'email.email' => 'El correo no es válido',
            'email.max' => 'Maximo 255 caracteres',
            'email.unique' => 'El correo ya está en uso',
            'username.required' => 'Obligatorio',
            'username.max' => 'Maximo 255 caracteres',
            'username.unique' => 'El nombre de usuario ya está en uso',
            'password.min' => 'Minimo 6 caracteres',
            'status.required' => 'Obligatorio',
        ];
    }
}
