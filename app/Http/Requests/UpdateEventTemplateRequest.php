<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'template_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('templates', 'id')->where('is_active', true)],
        ];
    }

    public function messages(): array
    {
        return [
            'template_id.required' => 'Vui lòng chọn mẫu thiệp.',
            'template_id.exists'   => 'Mẫu thiệp không tồn tại hoặc đã bị vô hiệu hóa.',
        ];
    }
}
