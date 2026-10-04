<?php

namespace App\Http\Requests\Model;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'uuid', Rule::exists('categories', 'id')],
            'description' => ['nullable', 'string', 'max:5000'],
            'price'       => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'currency'    => ['nullable', 'string', 'size:3'],
            'color'       => ['nullable', 'string', 'max:50'],
            'reference'   => ['nullable', 'string', 'max:50'],
        ];
    }
}
