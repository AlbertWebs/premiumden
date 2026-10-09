<?php

namespace App\Http\Controllers\Member;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfilePhotoController
{
    public function own(Request $request): BinaryFileResponse
    {
        return $this->serve($request->user());
    }

    public function member(Request $request, User $member): BinaryFileResponse
    {
        abort_unless($request->user()->canViewMember($member), 404);
        abort_unless($request->user()->is($member) || $member->profile?->is_listed, 404);
        return $this->serve($member);
    }

    private function serve(User $member): BinaryFileResponse
    {
        abort_unless($member->profile?->photo_path && Storage::disk('local')->exists($member->profile->photo_path), 404);
        $mime = Storage::disk('local')->mimeType($member->profile->photo_path) ?: 'application/octet-stream';
        abort_unless(str_starts_with($mime, 'image/'), 404);
        $response = response()->file(Storage::disk('local')->path($member->profile->photo_path), ['Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff']);
        $response->setPrivate();
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('Vary', 'Cookie');
        return $response;
    }
}
