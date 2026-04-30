<?php

namespace App\Enums;

enum DutyType: string
{
    case DUTY = 'adoracja';
    case READY = 'rezerwa';
    case SUSPEND = 'zawieszona';
}
