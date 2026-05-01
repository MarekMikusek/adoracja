<?php

declare(strict_types=1);

namespace App\Enums;

enum DutyType: string
{
    case DUTY = 'adoracja';
    case READY = 'rezerwa';
    case SUSPEND = 'zawieszona';
}
