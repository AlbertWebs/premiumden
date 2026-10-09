<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'These details do not match an account.']);
        }

        $user = Auth::user();
        if ($user->hasRole(UserRole::Member) && ! $user->hasActiveMembership()) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'Membership portal access is not active for this account.']);
        }

        $request->session()->regenerate();

        $destination = match (true) {
            $user->hasRole(UserRole::Member) => route('member.dashboard'),
            $user->hasRole(UserRole::Author) => route('author.articles.index'),
            $user->hasRole(UserRole::ContentAdministrator) => route('admin.articles.index'),
            default => route('admin.dashboard'),
        };

        return redirect()->intended($destination);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
