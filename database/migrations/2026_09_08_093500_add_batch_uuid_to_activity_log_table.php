<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * spatie/laravel-activitylog v4.x (the version now locked in
     * composer.lock, downgraded from v5 after this table was first created)
     * inserts into a "batch_uuid" column that the original v5-shaped
     * migration doesn't have — without it, every activity log write fails.
     */
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->uuid('batch_uuid')->nullable()->after('properties');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn('batch_uuid');
        });
    }
};
