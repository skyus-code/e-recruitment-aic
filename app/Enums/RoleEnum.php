<?php

namespace App\Enums;

enum RoleEnum: string
{
    case Pelamar = 'pelamar';
    case Admin = 'admin';
    case Manajer = 'manajer';

    public function label(): string
    {
        return match ($this) {
            RoleEnum::Pelamar => 'Pelamar',
            RoleEnum::Admin => 'Admin HRD',
            RoleEnum::Manajer => 'Manajer',
        };
    }

    public function dashboardRoute(): string
    {
        return $this->value.'.dashboard';
    }
}
