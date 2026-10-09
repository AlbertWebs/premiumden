<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MemberFirstAccessController
{
    public function create(string $email, string $token): View
    {
        $user = $this->authorizedUser($email, $token);

        return view('auth.member-first-access', compact('user', 'token'));
    }

    public function store(Request $request, string $email, string $token): RedirectResponse
    {
        $user = $this->authorizedUser($email, $token);
        $request->validate(['password' => ['required', 'confirmed', 'string', 'min:12']]);
        $user->forceFill(['password' => Hash::make($request->string('password')->value()), 'first_access_token_hash' => null, 'first_access_expires_at' => null])->save();

        return redirect()->route('login')->with('status', 'Your password is set. You can now sign in to your member space.');
    }

    private function authorizedUser(string $email, string $token): User
    {
        $user = User::where('email', $email)->whereNotNull('first_access_token_hash')->firstOrFail();
        abort_unless($user->first_access_expires_at?->isFuture(), 403);
        abort_unless(hash_equals($user->first_access_token_hash, hash('sha256', $token)), 403);

        return $user;
    }
}
