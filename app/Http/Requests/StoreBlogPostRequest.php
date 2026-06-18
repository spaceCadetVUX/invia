<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogPostRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $req = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'title'        => [$req, 'string', 'max:200'],
            'excerpt'      => ['nullable', 'string', 'max:500'],
            'content'      => [$req, 'string'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
