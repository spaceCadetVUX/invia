<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePaymentRequest;
use App\Models\Event;
use App\Models\Payment;
use App\Services\CouponService;
use App\Services\PaymentService;
use App\Services\PlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private CouponService  $couponService,
        private PlanService    $planService,
    ) {}

    public function upgradePage(Event $event): Response
    {
        $this->authorize('update', $event);

        return Inertia::render('Dashboard/Events/Upgrade', [
            'event'       => $event->only('id', 'slug', 'title'),
            'currentPlan' => $event->plan,
            'quota'       => $this->planService->getQuota($event),
        ]);
    }

    public function create(CreatePaymentRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $data   = $request->validated();
        $type   = $data['type'];
        $plan   = $data['plan'] ?? null;
        $coupon = null;

        if (!empty($data['coupon_code'])) {
            $coupon = $this->couponService->validate($data['coupon_code'], $type, $plan);
        }

        $checkoutUrl = $this->paymentService->createLink($event, $type, $plan, $coupon);

        return redirect()->away($checkoutUrl);
    }

    public function returnHandler(Event $event, Request $request): Response
    {
        $this->authorize('update', $event);

        $orderCode = $request->query('orderCode');
        $payment   = $orderCode
            ? Payment::where('gateway_ref', (string) $orderCode)->first()
            : null;

        return Inertia::render('Dashboard/Events/PaymentResult', [
            'event'  => $event->only('id', 'slug', 'title'),
            'status' => $payment?->status ?? 'pending',
            'plan'   => $payment?->plan,
            'type'   => $payment?->type,
        ]);
    }

    public function cancelHandler(Event $event): Response
    {
        $this->authorize('update', $event);

        Payment::where('event_id', $event->id)
            ->where('status', 'pending')
            ->latest()
            ->first()
            ?->update(['status' => 'cancelled']);

        return Inertia::render('Dashboard/Events/PaymentResult', [
            'event'  => $event->only('id', 'slug', 'title'),
            'status' => 'cancelled',
            'plan'   => null,
        ]);
    }

    public function couponPreview(Request $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $data = $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
            'type'        => ['required', Rule::in(['plan', 'extra'])],
            'plan'        => ['nullable', Rule::in(['basic', 'pro', 'premium'])],
        ]);

        return response()->json(
            $this->couponService->preview($data['coupon_code'], $data['type'], $data['plan'] ?? null)
        );
    }

    public function webhook(Request $request): JsonResponse
    {
        try {
            $this->paymentService->handleWebhook($request->all());
        } catch (\Throwable $e) {
            Log::error('PayOS webhook error: ' . $e->getMessage(), $request->all());
        }

        return response()->json(['success' => true]);
    }
}
