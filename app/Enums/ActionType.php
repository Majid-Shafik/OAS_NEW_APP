<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ActionType: string implements HasLabel, HasColor, HasIcon
{
    case INSERT = 'INSERT';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INSERT => 'إضافة',
            self::UPDATE => 'تعديل',
            self::DELETE => 'حذف',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::INSERT => 'success',
            self::UPDATE => 'warning',
            self::DELETE => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::INSERT => 'heroicon-o-plus-circle',
            self::UPDATE => 'heroicon-o-pencil-square',
            self::DELETE => 'heroicon-o-trash',
        };
    }
}
