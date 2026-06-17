<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventId = $this->route('event')?->id;

        return [
            'mode'        => ['required', Rule::in(['all', 'unsent', 'manual'])],
            'guest_ids'   => ['required_if:mode,manual', 'array'],
            'guest_ids.*' => ['integer', Rule::exists('guests', 'id')->where('event_id', $eventId)],
        ];
    }

    public function messages(): array
    {
        return [
            'mode.required'        => 'Vui lòng chọn chế độ gửi.',
            'guest_ids.required_if'=> 'Vui lòng chọn ít nhất một khách.',
            'guest_ids.*.exists'   => 'Một hoặc nhiều khách không hợp lệ.',
        ];
    }
}
