<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('application_references', function (Blueprint $table): void {
            $table->string('membership_number', 40)->nullable()->after('full_name');
            $table->string('response_status')->default('pending')->after('phone');
            $table->timestamp('responded_at')->nullable()->after('response_status');
        });
    }

    public function down(): void
    {
        Schema::table('application_references', function (Blueprint $table): void {
            $table->dropColumn(['membership_number', 'response_status', 'responded_at']);
        });
    }
};
