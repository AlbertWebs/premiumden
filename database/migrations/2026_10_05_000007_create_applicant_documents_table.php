<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('membership_applications', function (Blueprint $table): void {
            $table->string('document_upload_token_hash', 64)->nullable();
            $table->timestamp('document_upload_expires_at')->nullable();
        });

        Schema::create('applicant_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('membership_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_type', 24);
            $table->string('identity_type', 24)->nullable();
            $table->string('original_name', 180);
            $table->string('storage_path')->unique();
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size_bytes');
            $table->string('status', 32)->default('pending')->index();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['membership_application_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_documents');
        Schema::table('membership_applications', function (Blueprint $table): void {
            $table->dropColumn(['document_upload_token_hash', 'document_upload_expires_at']);
        });
    }
};
