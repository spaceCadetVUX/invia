<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->renameColumn('og_image_path', 'cover_image_path');
            $table->boolean('is_published')->default(false)->after('excerpt');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->renameColumn('cover_image_path', 'og_image_path');
            $table->dropColumn('is_published');
        });
    }
};
