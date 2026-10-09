<?php

namespace App\Http\Controllers\Public;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipReferenceValidationController
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'membership_number' => ['required', 'string', 'max:40'],
        ]);

        $valid = User::query()->currentMember()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($data['name']))])
            ->whereHas('membership', fn ($membership) => $membership->whereRaw('LOWER(number) = ?', [strtolower(trim($data['membership_number']))]))
            ->exists();

        return response()->json([
            'valid' => $valid,
            'message' => $valid
                ? 'Member verified. An invitation will be emailed when you submit your application.'
                : 'We could not match that name and membership number to a current member. Please check the details.',
        ]);
    }
}
