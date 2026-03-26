<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistance_request_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')
                ->unique()
                ->constrained('service_requests')
                ->cascadeOnDelete();

            $table->text('case_summary')->nullable();
            $table->decimal('requested_amount', 10, 2)->nullable();

            $table->text('assessment_notes')->nullable();
            $table->date('assessment_date')->nullable();

            $table->string('claimant_name')->nullable();
            $table->string('relationship_to_beneficiary')->nullable();
            $table->string('referral_destination')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistance_request_details');
    }
};
