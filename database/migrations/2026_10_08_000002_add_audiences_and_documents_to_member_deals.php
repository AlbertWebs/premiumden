<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('member_deals', function (Blueprint $table): void {
            $table->foreignId('minimum_package_id')->nullable()->after('application_instructions')->constrained('membership_packages')->nullOnDelete();
        });

        Schema::create('member_deal_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_deal_id')->constrained()->cascadeOnDelete();
            $table->string('original_name', 255);
            $table->string('path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_deal_documents');
        Schema::table('member_deals', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('minimum_package_id');
        });
    }
};
