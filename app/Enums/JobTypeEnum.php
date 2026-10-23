<?php

namespace App\Enums;

enum JobTypeEnum: string
{
    case FullTime = 'full-time';
    case PartTime = 'part-time';
    case Contract = 'contract';
    case Internship = 'internship';

    public function label(): string
    {
        return match ($this) {
            JobTypeEnum::FullTime => 'Full-Time',
            JobTypeEnum::PartTime => 'Part-Time',
            JobTypeEnum::Contract => 'Contract',
            JobTypeEnum::Internship => 'Internship',
        };
    }
}
