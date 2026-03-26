<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_status_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')
                ->constrained('service_requests')
                ->cascadeOnDelete();

            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('remarks')->nullable();

            $table->foreignId('acted_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('acted_at')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_status_logs');
    }
};
