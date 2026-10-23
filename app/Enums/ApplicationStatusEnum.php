<?php

namespace App\Enums;

enum ApplicationStatusEnum: string
{
    case Applied = 'applied';
    case Screening = 'screening';
    case Passed = 'passed';
    case Interview = 'interview';
    case Hired = 'hired';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            ApplicationStatusEnum::Applied => 'Dilamar',
            ApplicationStatusEnum::Screening => 'Screening',
            ApplicationStatusEnum::Passed => 'Lolos Seleksi',
            ApplicationStatusEnum::Interview => 'Interview',
            ApplicationStatusEnum::Hired => 'Diterima',
            ApplicationStatusEnum::Rejected => 'Ditolak',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            ApplicationStatusEnum::Applied => 'bg-blue-100 text-blue-900',
            ApplicationStatusEnum::Screening => 'bg-slate-100 text-slate-700',
            ApplicationStatusEnum::Passed => 'bg-emerald-100 text-emerald-700',
            ApplicationStatusEnum::Interview => 'bg-amber-100 text-amber-700',
            ApplicationStatusEnum::Hired => 'bg-emerald-600 text-white',
            ApplicationStatusEnum::Rejected => 'bg-red-100 text-red-600',
        };
    }

    /**
     * Step index within the applicant timeline (rejected is handled separately).
     */
    public function step(): int
    {
        return match ($this) {
            ApplicationStatusEnum::Applied => 0,
            ApplicationStatusEnum::Screening, ApplicationStatusEnum::Passed => 1,
            ApplicationStatusEnum::Interview => 2,
            ApplicationStatusEnum::Hired, ApplicationStatusEnum::Rejected => 3,
        };
    }
}
