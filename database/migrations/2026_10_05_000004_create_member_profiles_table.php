<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('member_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('industry')->nullable()->index();
            $table->string('business_category')->nullable()->index();
            $table->string('location')->nullable()->index();
            $table->json('interests')->nullable();
            $table->text('biography')->nullable();
            $table->boolean('is_listed')->default(true)->index();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_profiles');
    }
};
