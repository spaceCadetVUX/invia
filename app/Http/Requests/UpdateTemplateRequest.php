<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'           => ['sometimes', 'string', 'max:100'],
            'category'       => ['sometimes', 'string', 'max:50'],
            'blade_file'     => ['sometimes', 'string', 'max:80', 'regex:/^[a-z0-9_-]+$/'],
            'price'          => ['sometimes', 'integer', 'min:0'],
            'is_active'      => ['sometimes', 'boolean'],
            'thumbnail_path' => ['sometimes', 'nullable', 'string', 'max:400'],
        ];
    }
}
