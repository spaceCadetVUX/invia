<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings'             => ['required', 'array'],
            'settings.*'           => ['array'],
            'settings.*.x'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'settings.*.y'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'settings.*.font'      => ['nullable', 'string', 'max:80'],
            'settings.*.size'      => ['nullable', 'integer', 'min:8', 'max:200'],
            'settings.*.color'     => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{3,6}$/'],
            'settings.*.value'     => ['nullable', 'string', 'max:300'],
        ];
    }
}
