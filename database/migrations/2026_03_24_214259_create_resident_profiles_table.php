<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resident_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('barangay_id')
                ->constrained('barangays')
                ->restrictOnDelete();

            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();

            $table->string('sex');
            $table->date('birth_date');
            $table->string('birth_place')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('mobile_number', 30)->nullable();
            $table->string('occupation')->nullable();
            $table->string('citizenship')->default('Filipino');

            $table->text('current_address_line');
            $table->string('current_municipality')->default('Valencia City');
            $table->string('current_province')->default('Bukidnon');

            $table->text('permanent_address_line')->nullable();
            $table->string('permanent_barangay_name')->nullable();
            $table->string('permanent_municipality')->nullable();
            $table->string('permanent_province')->nullable();

            $table->string('profile_photo_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resident_profiles');
    }
};
