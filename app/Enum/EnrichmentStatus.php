<?php

namespace App\Enum;

enum EnrichmentStatus: string
{
    case Pending    = 'pending';
    case InProgress = 'in_progress';
    case Skipped    = 'skipped';
    case Invalid    = 'invalid';
    case Failed     = 'failed';
    case Done       = 'done';
}
