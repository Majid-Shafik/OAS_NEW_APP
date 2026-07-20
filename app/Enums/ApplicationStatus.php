<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ApplicationStatus: string implements HasColor, HasIcon, HasLabel
{
    case New = 'NEW';
    case Accept = 'ACCEPT';
    case Reject = 'REJECT';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::New => 'جديد',
            self::Accept => 'مقبول',
            self::Reject => 'مرفوض',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::New => 'warning',
            self::Accept => 'success',
            self::Reject => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::New => 'heroicon-m-sparkles',
            self::Accept => 'heroicon-m-check-circle',
            self::Reject => 'heroicon-m-x-circle',
        };
    }
}
