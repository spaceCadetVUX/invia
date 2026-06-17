<?php

namespace App\Services;

use App\Mail\PaymentConfirmMail;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PayOS\PayOS;

class PaymentService
{
    private PayOS $payOS;

    public function __construct()
    {
        $this->payOS = new PayOS(
            clientId:    config('payos.client_id'),
            apiKey:      config('payos.api_key'),
            checksumKey: config('payos.checksum_key'),
        );
    }

    public function createLink(Event $event, string $type, ?string $plan, ?Coupon $coupon): string
    {
        $priceKey = $type === 'extra' ? 'extra' : $plan;
        $original = config("plans.{$priceKey}.price");
        $amount   = $this->applyDiscount($original, $coupon);

        $payment = Payment::create([
            'user_id'         => $event->user_id,
            'event_id'        => $event->id,
            'type'            => $type,
            'plan'            => $type === 'plan' ? $plan : null,
            'amount'          => $amount,
            'amount_original' => $original,
            'coupon_id'       => $coupon?->id,
            'status'          => 'pending',
            'gateway'         => 'payos',
            'gateway_ref'     => null,
        ]);

        // Dùng payment.id làm orderCode
        $payment->update(['gateway_ref' => (string) $payment->id]);

        $label = config("plans.{$priceKey}.label");

        // SDK v2: createPaymentLink nhận array, trả array
        $response = $this->payOS->createPaymentLink([
            'orderCode'   => $payment->id,
            'amount'      => $amount,
            'description' => 'INVIA ' . strtoupper($priceKey),   // max 25 ký tự
            'items'       => [
                ['name' => "Gói {$label}", 'quantity' => 1, 'price' => $amount],
            ],
            'cancelUrl' => route('dashboard.events.payment.cancel', $event->slug),
            'returnUrl' => route('dashboard.events.payment.return', $event->slug),
        ]);

        return $response['checkoutUrl'];
    }

    public function handleWebhook(array $body): void
    {
        // SDK v2: verifyPaymentWebhookData throws nếu signature sai, trả $body['data'] array trực tiếp
        $data = $this->payOS->verifyPaymentWebhookData($body);

        // PayOS ping test khi setup webhook — orderCode = 1
        if (($data['orderCode'] ?? null) === 1) {
            return;
        }

        // Kiểm tra code từ body gốc (không phải từ $data)
        if (($body['code'] ?? '') !== '00') {
            Log::info('PayOS non-success webhook', ['code' => $body['code'] ?? '']);
            return;
        }

        $orderCode = $data['orderCode'];

        DB::transaction(function () use ($orderCode, $body) {
            $payment = Payment::where('gateway_ref', (string) $orderCode)
                ->lockForUpdate()
                ->first();

            if (!$payment) {
                Log::warning("PayOS webhook: payment not found for orderCode {$orderCode}");
                return;
            }

            if ($payment->status === 'paid') {
                return;  // idempotent guard
            }

            $payment->update([
                'status'          => 'paid',
                'paid_at'         => now(),
                'gateway_payload' => $body,
            ]);

            $this->activate($payment);
        });
    }

    private function activate(Payment $payment): void
    {
        $event = $payment->event()->with('user')->first();

        if ($event) {
            match ($payment->type) {
                'plan'  => $this->activatePlan($event, $payment->plan),
                'extra' => $this->activateExtra($event),
                default => null,
            };
        }

        if ($payment->coupon_id) {
            Coupon::where('id', $payment->coupon_id)->increment('used_count');
        }

        if ($event?->user?->email) {
            Mail::to($event->user->email)->send(new PaymentConfirmMail($event, $payment));
        }
    }

    private function activatePlan(Event $event, string $plan): void
    {
        $event->update([
            'plan'       => $plan,
            'expires_at' => app(PlanService::class)->computeExpiresAt($plan),
        ]);
    }

    private function activateExtra(Event $event): void
    {
        $event->increment('extra_guests', 100);
    }

    private function applyDiscount(int $original, ?Coupon $coupon): int
    {
        if (!$coupon) return $original;

        $discount = $coupon->discount_type === 'percent'
            ? (int) round($original * $coupon->discount_value / 100)
            : $coupon->discount_value;

        return max(1000, $original - $discount);
    }
}
