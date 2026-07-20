<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('tenant_database')) {
            $database = session('tenant_database');
            
            // Set the dynamic database name
            Config::set('database.connections.tenant.database', $database);
            
            // Purge the connection to apply the new database
            DB::purge('tenant');
            
            // Set as the default connection for this request
            DB::setDefaultConnection('tenant');
        }

        return $next($request);
    }
}
