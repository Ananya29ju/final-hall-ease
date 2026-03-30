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
        Schema::create('waitlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');
            
            // Event details
            $table->string('event_name');
            $table->string('event_department');
            $table->string('event_type');
            
            // Coordinator details
            $table->string('coordinator_name');
            $table->string('coordinator_phone');
            $table->string('coordinator_department');
            $table->string('coordinator_email');
            $table->string('coordinator_emergency_number');
            
            // Requirements
            $table->json('media_requirements')->nullable();
            $table->text('media_requirements_other')->nullable();
            $table->json('resources')->nullable();
            $table->text('resources_other')->nullable();
            
            // Waitlist state
            $table->enum('status', ['pending', 'notified', 'confirmed', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            $table->timestamps();

            // Indexing for performance
            $table->index(['hall_id', 'event_date']);
            $table->index(['user_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waitlists');
    }
};
