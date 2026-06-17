<?php

namespace App\Http\Requests;

use App\Enums\EventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'min:3', 'max:120'],
            'event_date'    => ['required', 'date'],
            'event_time'    => ['nullable', 'date_format:H:i'],
            'venue_name'    => ['nullable', 'string', 'max:150'],
            'venue_address' => ['nullable', 'string', 'max:300'],
            'event_type'    => ['required', Rule::in(array_column(EventType::cases(), 'value'))],
            'template_id'   => ['required', 'integer', Rule::exists('templates', 'id')->where('is_active', true)],
            'language'      => ['nullable', Rule::in(['vi'])],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'      => 'Vui lòng nhập tên sự kiện.',
            'title.min'           => 'Tên sự kiện phải có ít nhất 3 ký tự.',
            'event_date.required' => 'Vui lòng chọn ngày tổ chức.',
            'event_date.date'     => 'Ngày tổ chức không hợp lệ.',
            'event_type.required' => 'Vui lòng chọn loại sự kiện.',
            'template_id.required'=> 'Vui lòng chọn mẫu thiệp.',
            'template_id.exists'  => 'Mẫu thiệp không tồn tại hoặc đã bị vô hiệu hóa.',
        ];
    }
}
