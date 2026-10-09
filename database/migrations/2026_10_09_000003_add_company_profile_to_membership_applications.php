<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('membership_applications', function (Blueprint $table): void {
            $table->text('company_description')->nullable();
            $table->string('employee_count', 24)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('membership_applications', function (Blueprint $table): void {
            $table->dropColumn(['company_description', 'employee_count']);
        });
    }
};
