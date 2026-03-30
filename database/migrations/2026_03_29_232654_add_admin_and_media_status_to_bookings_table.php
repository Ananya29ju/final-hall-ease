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
            $table->enum('admin_status', ['pending', 'approved', 'rejected', 'kept_pending'])->default('pending')->after('booking_status');
            $table->enum('media_status', ['not_required', 'pending', 'accepted', 'rejected', 'kept_pending'])->default('not_required')->after('admin_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['admin_status', 'media_status']);
        });
    }
};
