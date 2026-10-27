<?php

namespace App\Enums;

enum MilitantStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Rejected = 'rejected';
}
