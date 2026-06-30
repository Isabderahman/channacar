<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Deposit = 'deposit';
    case Paid = 'paid';
}
