<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('membership_applications', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 24)->unique();
            $table->foreignId('membership_package_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('draft')->index();
            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone', 40);
            $table->string('company');
            $table->string('job_title');
            $table->string('industry');
            $table->string('location');
            $table->text('biography')->nullable();
            $table->timestamp('consent_at')->nullable();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('application_references', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('membership_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone', 40)->nullable();
            $table->timestamps();
        });

        Schema::create('application_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('membership_application_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_histories');
        Schema::dropIfExists('application_references');
        Schema::dropIfExists('membership_applications');
    }
};
