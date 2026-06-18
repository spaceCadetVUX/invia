<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:100'],
            'category'   => ['required', 'string', 'max:50'],
            'blade_file' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_-]+$/'],
            'price'      => ['required', 'integer', 'min:0'],
            'is_active'  => ['boolean'],
        ];
    }
}
