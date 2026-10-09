<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contact_enquiries', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('email')->index();
            $table->string('phone', 40)->nullable();
            $table->string('subject', 180);
            $table->text('message');
            $table->string('status')->default('new')->index();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('contact_enquiries'); }
};
