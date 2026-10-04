<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:150'],
            'slug'        => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('shops', 'slug'),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'whatsapp'    => ['nullable', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex'  => 'Le slug doit contenir uniquement des lettres minuscules, chiffres et tirets.',
            'slug.unique' => 'Ce slug est déjà utilisé.',
        ];
    }
}
