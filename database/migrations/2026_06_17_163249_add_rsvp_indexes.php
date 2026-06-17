<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rsvp', function (Blueprint $table) {
            $table->index(['event_id', 'status'],     'idx_rsvp_event_status');
            $table->index(['event_id', 'created_at'], 'idx_rsvp_event_created');
        });
    }

    public function down(): void
    {
        Schema::table('rsvp', function (Blueprint $table) {
            $table->dropIndex('idx_rsvp_event_status');
            $table->dropIndex('idx_rsvp_event_created');
        });
    }
};
