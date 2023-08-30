<?php

namespace App\Enums;

enum OperationType: string
{
    case Withdraw = 'withdraw';
    case Deposit = 'deposit';
}
