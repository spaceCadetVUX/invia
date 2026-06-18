<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'             => ['required', 'string', 'max:50',
                Rule::unique('coupons', 'code')->ignore($this->route('coupon'))],
            'discount_type'    => ['required', Rule::in(['percent', 'fixed'])],
            'discount_value'   => ['required', 'integer', 'min:1'],
            'applicable_plans' => ['nullable', 'string', 'max:100'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'expires_at'       => ['nullable', 'date'],
            'is_active'        => ['boolean'],
        ];
    }
}
