<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->timestamp('email_sent_at')->nullable()->after('source');
            $table->timestamp('unsubscribed_at')->nullable()->after('email_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn(['email_sent_at', 'unsubscribed_at']);
        });
    }
};
