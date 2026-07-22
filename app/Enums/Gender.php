<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasColor, HasIcon, HasLabel
{
    case Male = 'ذكر';
    case Female = 'انثى';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Male => 'ذكر',
            self::Female => 'أنثى',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Male => 'info',
            self::Female => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Male => 'heroicon-m-user',
            self::Female => 'heroicon-m-user',
        };
    }
}
