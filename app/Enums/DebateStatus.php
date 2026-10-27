<?php

namespace App\Enums;

enum DebateStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Moderated = 'moderated';
}
