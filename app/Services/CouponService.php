<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function validate(string $code, string $type, ?string $plan): Coupon
    {
        $coupon = Coupon::where('code', strtoupper($code))
            ->where('is_active', true)
            ->first();

        throw_unless($coupon, ValidationException::withMessages([
            'coupon_code' => ['Mã giảm giá không tồn tại hoặc đã hết hiệu lực.'],
        ]));

        throw_if(
            $coupon->expires_at && $coupon->expires_at->isPast(),
            ValidationException::withMessages(['coupon_code' => ['Mã giảm giá đã hết hạn.']])
        );

        throw_if(
            $coupon->max_uses && $coupon->used_count >= $coupon->max_uses,
            ValidationException::withMessages(['coupon_code' => ['Mã giảm giá đã hết lượt sử dụng.']])
        );

        if ($coupon->applicable_plans && $plan) {
            $allowed = explode(',', $coupon->applicable_plans);
            throw_unless(
                in_array($plan, $allowed),
                ValidationException::withMessages(['coupon_code' => ['Mã này không áp dụng cho gói đã chọn.']])
            );
        }

        return $coupon;
    }

    public function preview(string $code, string $type, ?string $plan): array
    {
        try {
            $coupon   = $this->validate($code, $type, $plan);
            $priceKey = $type === 'extra' ? 'extra' : $plan;
            $original = config("plans.{$priceKey}.price");
            $final    = $this->computeFinal($original, $coupon);

            return [
                'valid'    => true,
                'discount' => $original - $final,
                'final'    => $final,
                'label'    => $coupon->discount_type === 'percent'
                    ? "Giảm {$coupon->discount_value}%"
                    : 'Giảm ' . number_format($coupon->discount_value) . 'đ',
            ];
        } catch (ValidationException $e) {
            return ['valid' => false, 'message' => $e->errors()['coupon_code'][0]];
        }
    }

    public function computeFinal(int $original, Coupon $coupon): int
    {
        $discount = $coupon->discount_type === 'percent'
            ? (int) round($original * $coupon->discount_value / 100)
            : $coupon->discount_value;

        return max(1000, $original - $discount);
    }
}
