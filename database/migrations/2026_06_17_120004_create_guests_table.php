<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('table_no')->nullable();
            $table->string('token', 32)->unique();
            $table->string('source')->default('manual');
            $table->timestamps();

            $table->index(['event_id']);
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
