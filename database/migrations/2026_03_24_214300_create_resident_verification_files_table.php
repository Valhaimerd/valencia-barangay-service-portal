<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resident_verification_files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('resident_verification_id')
                ->constrained('resident_verifications')
                ->cascadeOnDelete();

            $table->string('file_type');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->string('review_status')->default('pending')->index();
            $table->text('reviewer_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resident_verification_files');
    }
};
