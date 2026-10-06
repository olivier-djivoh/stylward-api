<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class RequestDeletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password'      => ['required', 'string'],
            'reason'        => ['nullable', 'in:no_longer_needed,too_expensive,missing_features,switched_tool,bug,other'],
            'reason_detail' => ['nullable', 'string', 'max:500'],
        ];
    }
}
