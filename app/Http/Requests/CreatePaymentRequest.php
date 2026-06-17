<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type'        => ['required', Rule::in(['plan', 'extra'])],
            'plan'        => [
                Rule::requiredIf(fn () => $this->input('type') === 'plan'),
                'nullable',
                Rule::in(['basic', 'pro', 'premium']),
            ],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
