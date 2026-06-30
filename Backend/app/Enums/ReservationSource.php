<?php

namespace App\Enums;

enum ReservationSource: string
{
    case Web = 'web';
    case Phone = 'phone';
    case WalkIn = 'walkin';
}
