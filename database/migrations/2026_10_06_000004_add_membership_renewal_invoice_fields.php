<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->string('kind')->default('initial')->index();
            $table->foreignId('membership_id')->nullable()->constrained()->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void { $table->dropConstrainedForeignId('membership_id'); $table->dropColumn('kind'); });
    }
};
