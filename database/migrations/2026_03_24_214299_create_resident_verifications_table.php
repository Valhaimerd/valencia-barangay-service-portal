<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resident_verifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('resident_profile_id')
                ->unique()
                ->constrained('resident_profiles')
                ->cascadeOnDelete();

            $table->string('verification_method');
            $table->string('status')->default('pending_verification')->index();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('correction_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resident_verifications');
    }
};
