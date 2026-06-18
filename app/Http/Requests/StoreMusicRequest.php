<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMusicRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file'   => ['required', 'file', 'mimes:mp3', 'max:15360'],
            'title'  => ['required', 'string', 'max:100'],
            'artist' => ['nullable', 'string', 'max:100'],
            'mood'   => ['required', Rule::in(['romantic', 'classical', 'acoustic', 'traditional'])],
        ];
    }
}
