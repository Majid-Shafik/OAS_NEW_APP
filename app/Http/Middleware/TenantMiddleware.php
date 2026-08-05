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
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Try to get the database from the session first
        // (works for already-authenticated requests)
        $database = null;

        if (session()->has('tenant_database')) {
            $database = session('tenant_database');
        }

        // Fallback: try to read from input (e.g., during login form submission)
        if (! $database && $request->has('data.database')) {
            $database = $request->input('data.database');
        }

        // Apply database switching if we have a target
        if ($database) {
            Config::set('database.connections.tenant.database', $database);
            Config::set('database.connections.mysql.database', $database);

            DB::purge('tenant');
            DB::purge('mysql');

            DB::setDefaultConnection('tenant');
            \Illuminate\Support\Facades\Log::info('TenantMiddleware ran for URL: ' . $request->url() . ' | Database set to: ' . $database);
        } else {
            \Illuminate\Support\Facades\Log::info('TenantMiddleware ran for URL: ' . $request->url() . ' | NO DATABASE SET');
        }

        return $next($request);
    }
}
