<?php

namespace App\Helpers;

class PortalHelper
{
    /**
     * Get the active portal year dynamically from the active database connection.
     *
     * @return string
     */
    public static function getActiveYear(): string
    {
        return substr(config('database.connections.' . config('database.default') . '.database'), -4);
    }
}
