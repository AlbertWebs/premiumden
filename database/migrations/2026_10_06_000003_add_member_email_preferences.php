<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('email_message_notifications')->default(true);
            $table->boolean('email_society_updates')->default(true);
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void { $table->dropColumn(['email_message_notifications', 'email_society_updates']); });
    }
};
