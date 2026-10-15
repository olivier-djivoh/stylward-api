<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email', 'max:191'],
            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
            'password.min'   => 'Le mot de passe doit faire au moins 12 caractères.',
        ];
    }
}
