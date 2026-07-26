<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum FreezeStatus: int implements HasLabel, HasColor, HasIcon
{
    case UNFROZEN = 0;
    case FROZEN = 1;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::UNFROZEN => 'غير مجمد',
            self::FROZEN => 'مجمد',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::UNFROZEN => 'success',
            self::FROZEN => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::UNFROZEN => 'heroicon-o-lock-open',
            self::FROZEN => 'heroicon-o-lock-closed',
        };
    }
}
