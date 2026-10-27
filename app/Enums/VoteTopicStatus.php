<?php

namespace App\Enums;

enum VoteTopicStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Achieved = 'achieved';
}
