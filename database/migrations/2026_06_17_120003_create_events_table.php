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
            $table->string('event_type')->default('wedding');
            $table->string('slug')->unique();
            $table->string('title');
            $table->timestamp('date')->nullable();
            $table->string('venue')->nullable();
            $table->string('venue_map_url')->nullable();
            $table->jsonb('settings')->nullable();
            $table->string('music_type')->default('library');
            $table->string('music_source')->nullable();
            $table->string('livestream_url')->nullable();
            $table->string('og_image_path')->nullable();
            $table->string('plan')->default('free');
            $table->unsignedInteger('guest_quota')->default(50);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
