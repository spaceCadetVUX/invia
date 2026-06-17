<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // type: plan | extra | template
            $table->string('type', 20)->default('plan')->after('coupon_id');
            // plan trở thành nullable (extra/template không có plan)
            $table->string('plan', 20)->nullable()->change();
            // original amount trước discount
            $table->unsignedInteger('amount_original')->default(0)->after('amount');
            // raw webhook payload từ PayOS
            $table->jsonb('gateway_payload')->nullable()->after('gateway_ref');
            // template purchase (future)
            $table->foreignId('template_id')->nullable()->after('event_id')
                ->constrained()->nullOnDelete();

            $table->index(['event_id', 'status'], 'idx_payments_event_status');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_event_status');
            $table->dropConstrainedForeignId('template_id');
            $table->dropColumn(['type', 'amount_original', 'gateway_payload']);
            $table->string('plan', 20)->nullable(false)->change();
        });
    }
};
