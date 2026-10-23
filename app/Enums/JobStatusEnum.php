<?php

namespace App\Enums;

enum JobStatusEnum: string
{
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            JobStatusEnum::Open => 'Dibuka',
            JobStatusEnum::Closed => 'Ditutup',
        };
    }
}
