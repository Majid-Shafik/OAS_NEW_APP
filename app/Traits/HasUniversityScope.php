<?php

namespace App\Traits;

use App\Models\Scopes\UniversityScope;

trait HasUniversityScope
{
    /**
     * Boot the trait and apply the global scope.
     */
    protected static function bootHasUniversityScope()
    {
        static::addGlobalScope(new UniversityScope);
    }
}
