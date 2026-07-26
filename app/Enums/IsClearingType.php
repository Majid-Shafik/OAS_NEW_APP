<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum IsClearingType: int implements HasLabel, HasColor, HasIcon
{
    case NORMAL = 0;
    case CLEARING = 1;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NORMAL => 'اعتيادي',
            self::CLEARING => 'نظام المقاصة',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NORMAL => 'info',
            self::CLEARING => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::NORMAL => 'heroicon-o-academic-cap',
            self::CLEARING => 'heroicon-o-briefcase',
        };
    }
}
