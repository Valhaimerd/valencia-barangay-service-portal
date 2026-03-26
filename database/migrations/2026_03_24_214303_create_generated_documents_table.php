<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')
                ->unique()
                ->constrained('service_requests')
                ->cascadeOnDelete();

            $table->string('document_number')->nullable()->unique();
            $table->string('file_path')->nullable();

            $table->timestamp('generated_at')->nullable();

            $table->foreignId('prepared_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('printed_at')->nullable();

            $table->foreignId('printed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_documents');
    }
};
