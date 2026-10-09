<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('membership_application_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('status')->default('pending')->index();
            $table->char('currency', 3)->default('KES');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamp('issued_at');
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_amount', 12, 2);
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('provider_reference')->nullable()->unique();
            $table->string('status')->default('pending')->index();
            $table->char('currency', 3)->default('KES');
            $table->decimal('amount', 12, 2);
            $table->timestamp('confirmed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('provider_event_id')->nullable()->unique();
            $table->string('status')->index();
            $table->string('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        Schema::create('memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('membership_application_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('membership_package_id')->constrained()->restrictOnDelete();
            $table->string('number')->unique();
            $table->string('status')->default('active')->index();
            $table->date('started_at');
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('membership_cards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('membership_id')->unique()->constrained()->cascadeOnDelete();
            $table->uuid('identifier')->unique();
            $table->string('status')->default('active')->index();
            $table->string('physical_card_reference')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('welcome_packages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('membership_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending')->index();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('tracking_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('welcome_packages');
        Schema::dropIfExists('membership_cards');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
