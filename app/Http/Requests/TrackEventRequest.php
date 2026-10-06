<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event'    => ['required', 'in:view_shop,view_model,whatsapp_click'],
            'model_id' => ['nullable', 'uuid', 'exists:models,id'],
        ];
    }
}
