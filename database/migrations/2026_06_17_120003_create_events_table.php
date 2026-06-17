<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 120);
            $table->string('slug', 200)->unique();
            $table->string('event_type', 30)->default('wedding');
            $table->string('status', 20)->default('draft');
            $table->date('event_date');
            $table->time('event_time')->nullable();
            $table->string('venue_name', 150)->nullable();
            $table->string('venue_address', 300)->nullable();
            $table->string('language', 10)->default('vi');
            $table->jsonb('settings')->default('{}');
            $table->string('og_image_path', 300)->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('rsvp_enabled')->default(true);
            $table->boolean('wishes_enabled')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
