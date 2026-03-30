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
        Schema::table('bookings', function (Blueprint $table) {
            $table->text('media_feedback_reason')->after('media_status')->nullable();
            $table->json('unavailable_media_requirements')->after('media_feedback_reason')->nullable();
            $table->text('media_remarks')->after('unavailable_media_requirements')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['media_feedback_reason', 'unavailable_media_requirements', 'media_remarks']);
        });
    }
};
