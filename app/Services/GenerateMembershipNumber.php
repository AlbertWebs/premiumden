<?php

namespace App\Services;

use App\Models\Membership;
use Illuminate\Support\Str;

class GenerateMembershipNumber
{
    public function handle(): string
    {
        do {
            $number = 'PBD-'.now()->format('y').'-'.Str::upper(Str::random(8));
        } while (Membership::query()->where('number', $number)->exists());

        return $number;
    }
}
