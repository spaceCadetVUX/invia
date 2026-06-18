<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminCouponController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Coupons', [
            'coupons' => Coupon::orderByDesc('created_at')->get(),
        ]);
    }

    public function store(StoreCouponRequest $request): JsonResponse
    {
        $coupon = Coupon::create($request->validated());

        return response()->json($coupon, 201);
    }

    public function update(StoreCouponRequest $request, Coupon $coupon): JsonResponse
    {
        $coupon->update($request->validated());

        return response()->json($coupon->fresh());
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        $coupon->update(['is_active' => false]);

        return response()->json(['deactivated' => true]);
    }
}
