<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('membership_applications', function (Blueprint $table): void {
            $table->string('draft_token_hash', 64)->nullable()->unique();
            $table->timestamp('draft_expires_at')->nullable()->index();
        });
    }
    public function down(): void
    {
        Schema::table('membership_applications', function (Blueprint $table): void { $table->dropUnique(['draft_token_hash']); $table->dropColumn(['draft_token_hash', 'draft_expires_at']); });
    }
};
