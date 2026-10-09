<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table): void {
            $table->foreignId('membership_application_id')->nullable()->change();
        });

        DB::table('users')->where('role', 'member')->whereNotNull('membership_package_id')->orderBy('id')->each(function (object $user): void {
            if (DB::table('memberships')->where('user_id', $user->id)->exists()) {
                return;
            }

            do {
                $number = 'PBD-'.date('y').'-'.Str::upper(Str::random(8));
            } while (DB::table('memberships')->where('number', $number)->exists());

            DB::table('memberships')->insert([
                'user_id' => $user->id,
                'membership_application_id' => null,
                'membership_package_id' => $user->membership_package_id,
                'number' => $number,
                'status' => $user->membership_active ? 'active' : 'inactive',
                'started_at' => $user->created_at ? substr($user->created_at, 0, 10) : now()->toDateString(),
                'expires_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        // Keep assigned numbers and nullable application links: those memberships may now be in use.
    }
};
