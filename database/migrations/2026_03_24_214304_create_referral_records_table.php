<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')
                ->constrained('service_requests')
                ->cascadeOnDelete();

            $table->string('referred_to');
            $table->text('referral_notes')->nullable();
            $table->string('referral_status')->default('referred')->index();
            $table->timestamp('referred_at')->useCurrent();

            $table->foreignId('referred_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_records');
    }
};
