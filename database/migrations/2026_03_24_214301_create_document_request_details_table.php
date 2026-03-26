<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_request_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')
                ->unique()
                ->constrained('service_requests')
                ->cascadeOnDelete();

            $table->text('purpose')->nullable();

            $table->string('cedula_number')->nullable();
            $table->date('cedula_date')->nullable();
            $table->string('cedula_place')->nullable();

            $table->unsignedInteger('years_of_residency')->nullable();
            $table->unsignedInteger('months_of_residency')->nullable();

            $table->unsignedSmallInteger('jobseeker_availment_count')->default(0);
            $table->boolean('oath_required')->default(false);

            $table->decimal('payment_amount', 10, 2)->nullable();
            $table->string('official_receipt_number')->nullable();

            $table->foreignId('prepared_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('printed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_request_details');
    }
};
