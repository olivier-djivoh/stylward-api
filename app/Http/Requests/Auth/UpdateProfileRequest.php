<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => [
                'required',
                'email:rfc',
                'max:191',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone'      => ['nullable', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Cette adresse email est déjà utilisée par un autre compte.',
        ];
    }
}