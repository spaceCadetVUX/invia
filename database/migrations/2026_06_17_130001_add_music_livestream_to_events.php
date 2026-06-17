<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('music_type', 20)->default('none')->after('language');
            $table->string('music_source', 300)->nullable()->after('music_type');
            $table->string('livestream_url', 500)->nullable()->after('music_source');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['music_type', 'music_source', 'livestream_url']);
        });
    }
};
