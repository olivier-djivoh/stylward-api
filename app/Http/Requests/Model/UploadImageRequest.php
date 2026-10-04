<?php

namespace App\Http\Requests\Model;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120', // 5 Mo
                'dimensions:max_width=4000,max_height=4000',
            ],
            'type' => [
                'nullable',
                'string',
                'in:main,gallery',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'image.image'      => 'Le fichier doit être une image valide.',
            'image.mimes'      => 'Formats acceptés : JPG, PNG, WebP.',
            'image.max'        => 'L\'image ne doit pas dépasser 5 Mo.',
            'image.dimensions' => 'L\'image ne doit pas dépasser 4000×4000 pixels.',
        ];
    }
}
