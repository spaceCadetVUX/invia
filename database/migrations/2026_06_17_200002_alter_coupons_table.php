<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->renameColumn('type',  'discount_type');
            $table->renameColumn('value', 'discount_value');
            $table->string('applicable_plans', 100)->nullable()->after('discount_value');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('applicable_plans');
            $table->renameColumn('discount_type',  'type');
            $table->renameColumn('discount_value', 'value');
        });
    }
};
