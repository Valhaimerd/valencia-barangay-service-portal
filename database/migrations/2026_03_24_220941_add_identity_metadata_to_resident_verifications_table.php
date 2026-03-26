<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resident_verifications', function (Blueprint $table) {
            $table->string('identity_document_label')->nullable()->after('verification_method');
            $table->string('identity_document_number')->nullable()->after('identity_document_label');
            $table->string('proof_of_residency_label')->nullable()->after('identity_document_number');
        });
    }

    public function down(): void
    {
        Schema::table('resident_verifications', function (Blueprint $table) {
            $table->dropColumn([
                'identity_document_label',
                'identity_document_number',
                'proof_of_residency_label',
            ]);
        });
    }
};
