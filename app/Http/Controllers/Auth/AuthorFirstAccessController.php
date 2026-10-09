<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthorFirstAccessController
{
    public function create(string $email, string $token): View
    {
        $author = $this->authorizedAuthor($email, $token);
        return view('auth.author-first-access', compact('author', 'token'));
    }

    public function store(Request $request, string $email, string $token): RedirectResponse
    {
        $author = $this->authorizedAuthor($email, $token);
        $request->validate(['password' => ['required', 'confirmed', 'string', 'min:12']]);
        $author->forceFill(['password' => Hash::make($request->string('password')->value()), 'email_verified_at' => now(), 'first_access_token_hash' => null, 'first_access_expires_at' => null])->save();
        return redirect()->route('login')->with('status', 'Your author password is ready. You can now sign in.');
    }

    private function authorizedAuthor(string $email, string $token): User
    {
        $author = User::where('email', $email)->where('role', UserRole::Author)->whereNotNull('first_access_token_hash')->firstOrFail();
        abort_unless($author->first_access_expires_at?->isFuture(), 403);
        abort_unless(hash_equals($author->first_access_token_hash, hash('sha256', $token)), 403);
        return $author;
    }
}
