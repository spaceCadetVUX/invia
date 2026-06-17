<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('yes');
            $table->unsignedTinyInteger('plus_one')->default(0);
            $table->string('dietary')->nullable();
            $table->timestamps();

            $table->unique(['guest_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvp');
    }
};
