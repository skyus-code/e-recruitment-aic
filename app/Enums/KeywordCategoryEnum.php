<?php

namespace App\Enums;

enum KeywordCategoryEnum: string
{
    case HardSkill = 'hard_skill';
    case SoftSkill = 'soft_skill';

    public function label(): string
    {
        return match ($this) {
            KeywordCategoryEnum::HardSkill => 'Hard Skill',
            KeywordCategoryEnum::SoftSkill => 'Soft Skill',
        };
    }
}
