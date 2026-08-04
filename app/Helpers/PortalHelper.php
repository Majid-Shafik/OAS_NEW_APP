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

    /**
     * Get the portal prefix dynamically from the active database connection.
     * Extracts the type (e.g., 'p', 'g') and year (e.g., '2022').
     * Handles formats like test_p_oas_db_2022 -> p2022
     *
     * @return string
     */
    public static function getPortalPrefix(): string
    {
        $dbName = config('database.connections.tenant.database') ?? config('database.connections.' . config('database.default') . '.database');
        
        if (preg_match('/_([a-z])_oas_db_(\d{4})$/', $dbName, $matches)) {
            return $matches[1] . $matches[2];
        }
        
        return 'p' . substr($dbName, -4); // Fallback
    }
}
