<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ApplicantStatus: string implements HasColor, HasIcon, HasLabel
{
    case New = 'NEW';
    case Ready = 'READY';
    case Updated = 'UPDATED';
    case Canceled = 'CANCELED'; // Just in case it's used

    public function getLabel(): ?string
    {
        return match ($this) {
            self::New => 'جديد',
            self::Ready => 'جاهز للاعتماد',
            self::Updated => 'محدث',
            self::Canceled => 'ملغي',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::New => 'info',
            self::Ready => 'success',
            self::Updated => 'warning',
            self::Canceled => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::New => 'heroicon-m-sparkles',
            self::Ready => 'heroicon-m-check-badge',
            self::Updated => 'heroicon-m-arrow-path',
            self::Canceled => 'heroicon-m-x-circle',
        };
    }
}
