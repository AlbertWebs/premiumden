<?php

namespace App\Http\Controllers\Public;

use App\Models\ApplicationReference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplicationReferenceResponseController
{
    public function __invoke(Request $request, ApplicationReference $reference, string $response): RedirectResponse
    {
        abort_unless(in_array($response, ['accepted', 'declined'], true), 404);
        abort_unless($reference->member_user_id !== null, 404);

        if ($reference->response_status === 'pending') {
            $reference->forceFill(['response_status' => $response, 'responded_at' => now()])->save();
        }

        return redirect()->route('application.reference.response', ['response' => $response]);
    }
}
