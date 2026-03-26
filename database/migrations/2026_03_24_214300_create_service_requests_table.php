<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();

            $table->string('reference_number')->unique();

            $table->foreignId('resident_profile_id')
                ->constrained('resident_profiles')
                ->cascadeOnDelete();

            $table->foreignId('service_type_id')
                ->constrained('service_types')
                ->restrictOnDelete();

            $table->foreignId('barangay_id')
                ->constrained('barangays')
                ->restrictOnDelete();

            $table->string('request_category')->index();
            $table->string('current_status')->default('submitted')->index();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('latest_status_at')->nullable();

            $table->foreignId('assigned_to_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('approved_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('rejected_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('cancelled_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('rejection_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('internal_notes')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
