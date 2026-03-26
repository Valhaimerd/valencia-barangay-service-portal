<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('release_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')
                ->unique()
                ->constrained('service_requests')
                ->cascadeOnDelete();

            $table->string('released_to_name')->nullable();
            $table->string('released_to_relationship')->nullable();
            $table->timestamp('released_at')->nullable();

            $table->foreignId('released_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('claimant_identification_notes')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('release_records');
    }
};
