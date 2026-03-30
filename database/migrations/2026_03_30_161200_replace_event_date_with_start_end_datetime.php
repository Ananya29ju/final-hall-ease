<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add new columns as nullable first
        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('start_datetime')->nullable()->after('created_by');
            $table->dateTime('end_datetime')->nullable()->after('start_datetime');
        });

        // Step 2: Migrate existing data: combine event_date + start_time / end_time into datetime columns
        DB::statement("
            UPDATE bookings
            SET start_datetime = CONCAT(event_date, ' ', COALESCE(start_time, '00:00:00')),
                end_datetime   = CONCAT(event_date, ' ', COALESCE(end_time, '23:59:59'))
            WHERE event_date IS NOT NULL
        ");

        // For any rows where event_date was null, set a default
        DB::statement("
            UPDATE bookings
            SET start_datetime = COALESCE(start_datetime, NOW()),
                end_datetime   = COALESCE(end_datetime, NOW())
        ");

        // Step 3: Drop old index (handle case where it may not exist)
        try {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropIndex('bookings_hall_id_event_date_index');
            });
        } catch (\Exception $e) {
            // Index may not exist
        }

        // Step 4: Drop old columns
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('event_date');
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
        });

        // Step 5: Add new index
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['hall_id', 'start_datetime', 'end_datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropIndex('bookings_hall_id_start_datetime_end_datetime_index');
            });
        } catch (\Exception $e) {
            // Index may not exist
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->date('event_date')->nullable()->after('created_by');
            $table->time('start_time')->nullable()->after('event_date');
            $table->time('end_time')->nullable()->after('start_time');
        });

        // Migrate data back
        DB::statement("
            UPDATE bookings
            SET event_date = DATE(start_datetime),
                start_time = TIME(start_datetime),
                end_time   = TIME(end_datetime)
            WHERE start_datetime IS NOT NULL
        ");

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['start_datetime', 'end_datetime']);
        });

        try {
            Schema::table('bookings', function (Blueprint $table) {
                $table->index(['hall_id', 'event_date']);
            });
        } catch (\Exception $e) {
            // ignore
        }
    }
};
