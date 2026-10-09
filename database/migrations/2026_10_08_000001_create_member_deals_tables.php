<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('member_deals', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->string('slug', 200)->unique();
            $table->string('partner', 180);
            $table->string('category', 80)->index();
            $table->string('summary', 500);
            $table->text('details');
            $table->text('member_value');
            $table->text('application_instructions')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('closes_at')->nullable()->index();
            $table->boolean('is_active')->default(false)->index();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('member_deal_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_deal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamp('applied_at');
            $table->timestamps();
            $table->unique(['member_deal_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_deal_applications');
        Schema::dropIfExists('member_deals');
    }
};
