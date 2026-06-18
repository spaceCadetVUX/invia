<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('meta_title', 70)->nullable()->after('title');
            $table->string('meta_description', 320)->nullable()->after('excerpt');
            $table->json('faq')->nullable()->after('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'faq']);
        });
    }
};
