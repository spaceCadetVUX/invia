<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['sometimes', 'string', 'max:100'],
            'email'    => ['nullable', 'email', 'max:150'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'table_no' => ['nullable', 'string', 'max:20'],
        ];
    }
}
