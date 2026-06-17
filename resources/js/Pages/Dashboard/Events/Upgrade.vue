<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
    event:       Object,
    currentPlan: String,
    quota:       Object,
})

const plans = [
    { key: 'basic',   label: 'Basic',   price: 99_000,  guests: 200,  features: ['200 khách', 'Gửi email thiệp'] },
    { key: 'pro',     label: 'Pro',     price: 199_000, guests: 500,  features: ['500 khách', 'Gửi email thiệp', 'Export Excel', 'Quản lý bàn tiệc'] },
    { key: 'premium', label: 'Premium', price: 399_000, guests: 1000, features: ['1,000 khách', 'Tất cả tính năng', 'Lưu trữ vĩnh viễn'] },
]

const selectedPlan = ref(null)
const couponCode   = ref('')
const couponResult = ref(null)
const checking     = ref(false)
const submitting   = ref(false)

const finalPrice = computed(() => {
    if (!selectedPlan.value) return null
    if (couponResult.value?.valid) return couponResult.value.final
    return plans.find(p => p.key === selectedPlan.value)?.price ?? null
})

async function checkCoupon() {
    if (!couponCode.value || !selectedPlan.value) return
    checking.value = true
    couponResult.value = null
    try {
        const res = await axios.post(
            route('dashboard.events.payment.coupon-preview', props.event.slug),
            { coupon_code: couponCode.value, type: 'plan', plan: selectedPlan.value }
        )
        couponResult.value = res.data
    } catch {
        couponResult.value = { valid: false, message: 'Không thể kiểm tra mã. Vui lòng thử lại.' }
    } finally {
        checking.value = false
    }
}

function selectPlan(key) {
    selectedPlan.value = key
    couponResult.value = null
}

function checkout() {
    if (!selectedPlan.value || submitting.value) return
    submitting.value = true
    router.post(
        route('dashboard.events.payment.create', props.event.slug),
        {
            type:        'plan',
            plan:        selectedPlan.value,
            coupon_code: couponResult.value?.valid ? couponCode.value : null,
        }
    )
}

function buyExtra() {
    if (submitting.value) return
    submitting.value = true
    router.post(
        route('dashboard.events.payment.create', props.event.slug),
        { type: 'extra' }
    )
}
</script>

<template>
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-gray-900">Nâng gói sự kiện</h1>
            <p class="text-sm text-gray-500 mt-1">
                Gói hiện tại: <strong class="capitalize">{{ currentPlan }}</strong>
                &nbsp;·&nbsp; {{ quota.current_guests }}/{{ quota.max_guests }} khách
            </p>
        </div>

        <!-- Plan cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div
                v-for="plan in plans"
                :key="plan.key"
                @click="selectPlan(plan.key)"
                class="border rounded-xl p-5 cursor-pointer transition-all"
                :class="{
                    'border-blue-500 ring-2 ring-blue-200 bg-blue-50': selectedPlan === plan.key,
                    'border-gray-200 hover:border-gray-300 bg-white':  selectedPlan !== plan.key,
                    'opacity-40 pointer-events-none':                   currentPlan === plan.key,
                }"
            >
                <div class="font-semibold text-lg mb-1">{{ plan.label }}</div>
                <div class="text-2xl font-bold text-blue-600 mb-3">
                    {{ plan.price.toLocaleString('vi') }}đ
                </div>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li v-for="f in plan.features" :key="f" class="flex items-center gap-1.5">
                        <span class="text-green-500 font-bold">✓</span> {{ f }}
                    </li>
                </ul>
                <div v-if="currentPlan === plan.key" class="text-xs text-gray-400 mt-3 font-medium">
                    Gói hiện tại
                </div>
            </div>
        </div>

        <!-- Coupon -->
        <div v-if="selectedPlan" class="mb-6">
            <label class="text-sm font-medium text-gray-700">Mã giảm giá</label>
            <div class="flex gap-2 mt-1">
                <input
                    v-model="couponCode"
                    @keyup.enter="checkCoupon"
                    placeholder="Nhập mã..."
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm flex-1 uppercase focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button
                    @click="checkCoupon"
                    :disabled="checking || !couponCode"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50"
                >
                    {{ checking ? '...' : 'Áp dụng' }}
                </button>
            </div>
            <p v-if="couponResult?.valid" class="text-sm text-green-600 mt-1">
                ✓ {{ couponResult.label }} — Còn lại {{ couponResult.final.toLocaleString('vi') }}đ
            </p>
            <p v-else-if="couponResult?.valid === false" class="text-sm text-red-500 mt-1">
                {{ couponResult.message }}
            </p>
        </div>

        <!-- Checkout button -->
        <button
            v-if="selectedPlan"
            @click="checkout"
            :disabled="submitting"
            class="w-full py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 disabled:opacity-50 transition"
        >
            {{ submitting ? 'Đang chuyển hướng...' : `Thanh toán ${finalPrice?.toLocaleString('vi')}đ qua PayOS` }}
        </button>

        <!-- Extra guests -->
        <div class="mt-8 border-t pt-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-medium text-gray-900">Extra +100 khách</div>
                    <div class="text-sm text-gray-500">Thêm slot — không thay đổi gói hiện tại</div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-blue-600">49,000đ</div>
                    <button
                        @click="buyExtra"
                        :disabled="submitting"
                        class="mt-1 px-3 py-1.5 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50"
                    >
                        Mua thêm
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
