<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRsvpRequest extends FormRequest
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
            'name'     => $nameRule,
            'email'    => ['nullable', 'email', 'max:150'],
            'status'   => ['required', Rule::in(['yes', 'no', 'maybe'])],
            'plus_one' => ['nullable', 'integer', 'min:0', 'max:10'],
            'dietary'  => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'status.required' => 'Vui lòng chọn trạng thái tham dự.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];
    }
}
