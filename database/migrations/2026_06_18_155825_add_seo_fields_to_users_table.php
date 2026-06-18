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
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('avatar_url');
            $table->string('job_title', 100)->nullable()->after('bio');
            $table->string('website', 255)->nullable()->after('job_title');
            $table->string('twitter_url', 255)->nullable()->after('website');
            $table->string('linkedin_url', 255)->nullable()->after('twitter_url');
            $table->string('facebook_url', 255)->nullable()->after('linkedin_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bio', 'job_title', 'website', 'twitter_url', 'linkedin_url', 'facebook_url']);
        });
    }
};
