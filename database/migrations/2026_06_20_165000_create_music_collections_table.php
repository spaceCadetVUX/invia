<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('music_collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->timestamps();
        });

        Schema::create('music_collection_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('music_collections')->cascadeOnDelete();
            $table->foreignId('track_id')->constrained('music_library')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unique(['collection_id', 'track_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('music_collection_tracks');
        Schema::dropIfExists('music_collections');
    }
};
