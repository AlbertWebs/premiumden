<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Mail\AuthorAccessInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthorController
{
    public function index(): View { return view('admin.authors.index', ['authors' => User::where('role', UserRole::Author)->withCount('articles')->orderBy('name')->paginate(20)]); }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:160'], 'email' => ['required', 'email:rfc', 'max:190', 'unique:users,email']]);
        $author = User::create(['name' => $data['name'], 'email' => strtolower($data['email']), 'password' => Str::random(64), 'role' => UserRole::Author, 'membership_active' => false]);
        $token = Str::random(64);
        $expiresAt = now()->addHours(48);
        $author->forceFill(['first_access_token_hash' => hash('sha256', $token), 'first_access_expires_at' => $expiresAt])->save();
        app(\App\Services\RecordAuditEvent::class)->handle('author.invited', $author, [], $request->user()->id);
        $setupUrl = URL::temporarySignedRoute('author.first-access.create', $expiresAt, ['email' => $author->email, 'token' => $token]);
        try {
            Mail::to($author->email)->send(new AuthorAccessInvitation($author, $setupUrl, $expiresAt->format('j F Y, H:i T')));
        } catch (\Throwable $exception) {
            Log::error('Unable to send the new author setup link.', ['user_id' => $author->id, 'exception' => $exception::class]);
            return back()->with('status', 'Author account created, but the setup email could not be sent. Check mail settings before inviting them again.');
        }

        return back()->with('status', 'Author account created. A private setup link has been sent.');
    }
}
