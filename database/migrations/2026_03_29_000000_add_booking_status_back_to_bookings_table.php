<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('bookings', 'booking_status')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('booking_status')->default('pending')->after('end_time');
            });
        }

        DB::table('bookings')
            ->whereNotNull('cancellation_reason')
            ->where('cancellation_reason', '!=', '')
            ->update(['booking_status' => 'cancelled']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'booking_status')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('booking_status');
            });
        }
    }
};
