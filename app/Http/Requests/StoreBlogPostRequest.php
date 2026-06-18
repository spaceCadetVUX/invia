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
            'author_id'    => ['nullable', 'exists:users,id'],
            'title'        => [$req, 'string', 'max:200'],
            'excerpt'      => ['nullable', 'string', 'max:500'],
            'content'      => [$req, 'string'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'cover_image'        => ['nullable', 'image', 'max:2048'],
            'meta_title'         => ['nullable', 'string', 'max:70'],
            'meta_description'   => ['nullable', 'string', 'max:320'],
            'faq'                => ['nullable', 'array'],
            'faq.*.question'     => ['required_with:faq', 'string', 'max:500'],
            'faq.*.answer'       => ['required_with:faq', 'string', 'max:2000'],
        ];
    }
}
