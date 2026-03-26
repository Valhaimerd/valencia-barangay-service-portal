<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')
                ->constrained('service_requests')
                ->cascadeOnDelete();

            $table->decimal('amount', 10, 2);
            $table->string('payment_status')->default('pending')->index();
            $table->string('official_receipt_number')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->foreignId('received_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_records');
    }
};
