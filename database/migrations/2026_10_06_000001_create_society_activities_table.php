<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('society_activities', function (Blueprint $table): void {
            $table->id(); $table->string('title', 180); $table->string('slug')->unique();
            $table->string('category', 80); $table->string('excerpt', 500)->nullable(); $table->longText('body');
            $table->string('visibility')->default('public')->index(); $table->string('status')->default('draft')->index();
            $table->string('featured_image_path')->nullable(); $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('society_activities'); }
};
