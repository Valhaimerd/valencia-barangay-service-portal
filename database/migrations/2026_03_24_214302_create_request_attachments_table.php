<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')
                ->constrained('service_requests')
                ->cascadeOnDelete();

            $table->string('attachment_type');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->foreignId('uploaded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->boolean('is_required')->default(false);
            $table->string('review_status')->default('pending')->index();
            $table->text('reviewer_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_attachments');
    }
};
