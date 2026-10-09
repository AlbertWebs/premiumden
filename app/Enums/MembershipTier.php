<?php

namespace App\Enums;

enum MembershipTier: string
{
    case Diamond = 'diamond';
    case Gold = 'gold';
    case Platinum = 'platinum';

    public function accessRank(): int
    {
        return match ($this) {
            self::Diamond => 1,
            self::Gold => 2,
            self::Platinum => 3,
        };
    }
}
