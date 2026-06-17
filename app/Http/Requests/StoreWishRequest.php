<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $nameRule = $this->query('t')
            ? ['nullable', 'string', 'max:100']
            : ['required', 'string', 'max:100'];

        return [
            'name'    => $nameRule,
            'message' => ['required', 'string', 'min:2', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Vui lòng nhập tên của bạn.',
            'message.required' => 'Vui lòng nhập lời chúc.',
            'message.min'      => 'Lời chúc phải có ít nhất 2 ký tự.',
            'message.max'      => 'Lời chúc không được quá 500 ký tự.',
        ];
    }
}
