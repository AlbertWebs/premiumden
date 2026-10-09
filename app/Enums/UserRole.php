<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdministrator = 'super_administrator';
    case RiskTeam = 'risk_team';
    case MembershipAdministrator = 'membership_administrator';
    case ContentAdministrator = 'content_administrator';
    case Author = 'author';
    case Member = 'member';
}
