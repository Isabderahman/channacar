<?php

namespace App\Enums;

enum ExtraBillingType: string
{
    case PerDay = 'per_day';
    case OneTime = 'one_time';
}
