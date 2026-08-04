<?php

use App\Helpers\PortalHelper;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// مسار صريح لخدمة ملف Livewire JS بدون أي middleware
// (حل لمشكلة 404 الناتجة عن تعارض middleware مع مسارات Livewire)
Route::get('/livewire-{version}/livewire.js', function (string $version) {
    // ابحث عن ملف livewire.js داخل حزمة Livewire
    $possiblePaths = [
        base_path("vendor/livewire/livewire/dist/livewire.js"),
        base_path("vendor/livewire/livewire/dist/livewire.min.js"),
    ];
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            return response()->file($path, [
                'Content-Type' => 'application/javascript',
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        }
    }
    return response('// Livewire JS not found at expected paths', 404, ['Content-Type' => 'application/javascript']);
})->withoutMiddleware(['*']);

    Route::get('/debug-auth', function () {
        $user = Auth::user();
        $permissions = $user ? $user->getAllPermissions()->pluck('name') : [];
        return response()->json([
            'logged_in'   => Auth::check(),
            'user_id'     => optional($user)->USER_IDENT,
            'email'       => optional($user)->LOGON_ID,
            'roles'       => optional($user)->getRoleNames(),
            'permissions' => $permissions,
            'session_tenant_db' => session('tenant_database'),
            'current_db' => \Illuminate\Support\Facades\DB::connection()->getDatabaseName(),
        ]);
    });

    // مسار مؤقت لتسجيل الدخول بالقوة (للاختبار فقط)
    Route::get('/force-login/{userId}', function ($userId) {
        // قاعدة البيانات المستهدفة - أخذها من request أو الافتراضية
        $db = request('db', config('academic_years.default_database', 'test_p_oas_db_2022'));

        // ضبط اتصال قاعدة البيانات
        \Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $db);
        \Illuminate\Support\Facades\Config::set('database.connections.mysql.database', $db);
        \Illuminate\Support\Facades\DB::purge('tenant');
        \Illuminate\Support\Facades\DB::purge('mysql');
        \Illuminate\Support\Facades\DB::setDefaultConnection('tenant');

        $user = \App\Models\User::find($userId);
        if (! $user) {
            return response()->json(['error' => 'User not found in DB: ' . $db], 404);
        }

        // تسجيل الدخول وحفظ الجلسة
        Auth::login($user);
        session()->put('tenant_database', $db);  // ← حفظ قاعدة البيانات في الجلسة
        session()->save();

        // توجيه مباشر للوحة التحكم بعد نجاح الدخول
        return redirect('/admin');
    });

    // مسار مؤقت لتفريغ الـ cache ونشر assets
    Route::get('/run-artisan', function () {
        if (app()->environment('production')) {
            abort(403, 'Not allowed in production');
        }
        $commands = ['route:clear', 'config:clear', 'cache:clear', 'view:clear', 'permission:cache-reset'];
        $output = [];
        foreach ($commands as $cmd) {
            \Illuminate\Support\Facades\Artisan::call($cmd);
            $output[$cmd] = trim(\Illuminate\Support\Facades\Artisan::output()) ?: 'Done';
        }

        // ←←← نشر Livewire JS assets إلى مجلد public/ لأن nginx يخدم .js من public فقط ←←←
        $livewireAssets = [];
        $livewireDist = base_path('vendor/livewire/livewire/dist');

        // اكتشاف hash المسار من المسارات المسجلة
        $livewireHash = null;
        foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
            if (preg_match('#livewire-([a-f0-9]+)/livewire\.js#', $route->uri(), $m)) {
                $livewireHash = $m[1];
                break;
            }
        }

        if ($livewireHash && is_dir($livewireDist)) {
            $publicDir = public_path("livewire-{$livewireHash}");
            if (!is_dir($publicDir)) {
                mkdir($publicDir, 0755, true);
            }

            $files = ['livewire.js', 'livewire.min.js', 'livewire.min.js.map', 'livewire.csp.min.js.map'];
            foreach ($files as $file) {
                $src = "{$livewireDist}/{$file}";
                $dst = "{$publicDir}/{$file}";
                if (file_exists($src)) {
                    copy($src, $dst);
                    $livewireAssets[$file] = "Copied → public/livewire-{$livewireHash}/{$file}";
                } else {
                    $livewireAssets[$file] = "Source not found: {$src}";
                }
            }
        } else {
            $livewireAssets['error'] = "Hash: {$livewireHash}, Dist dir exists: " . (is_dir($livewireDist) ? 'yes' : 'no');
        }

        return response()->json([
            'msg'            => 'Cache cleared + Livewire assets published',
            'output'         => $output,
            'livewire_hash'  => $livewireHash,
            'livewire_assets' => $livewireAssets,
        ]);
    });

    // فحص إعدادات البيئة والـ assets URLs
    Route::get('/debug-env', function () {
        // فحص هل مسار Livewire مسجل
        $livewireRoutes = collect(\Illuminate\Support\Facades\Route::getRoutes())->map(function ($route) {
            return $route->uri();
        })->filter(function ($uri) {
            return str_contains($uri, 'livewire');
        })->values();

        // توليد script tag بطريقة Livewire
        $scriptHtml = null;
        try {
            ob_start();
            echo \Livewire\Livewire::scripts();
            $scriptHtml = ob_get_clean();
        } catch (\Throwable $e) {
            $scriptHtml = 'Error generating: ' . $e->getMessage();
        }

        // فحص هل TenantMiddleware في global web
        $globalMiddleware = app(\Illuminate\Foundation\Http\Kernel::class)->getMiddlewareGroups()['web'] ?? [];

        return response()->json([
            'APP_URL'              => config('app.url'),
            'ASSET_URL'            => config('app.asset_url'),
            'APP_ENV'              => config('app.env'),
            'request_root'         => request()->root(),
            'livewire_routes_count' => $livewireRoutes->count(),
            'livewire_routes'       => $livewireRoutes,
            'livewire_script_tag'   => $scriptHtml ? substr($scriptHtml, 0, 500) : null,
            'global_web_middleware' => $globalMiddleware,
        ]);
    });

    // مسار تشخيص شامل للقوائم والصلاحيات
    Route::get('/debug-nav', function () {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Not authenticated - visit /force-login/55562?db=test_p_oas_db_2022 first']);
        }

        $results = [
            'user_id'   => $user->USER_IDENT,
            'current_db' => \Illuminate\Support\Facades\DB::connection()->getDatabaseName(),
            'session_db' => session('tenant_database'),
            'roles'     => $user->getRoleNames(),
            'resources' => [],
            'gate_checks' => [],
            'permission_table_rows' => [],
            'errors'    => [],
        ];

        // فحص Gate مباشرة
        try {
            $results['gate_checks'] = [
                'viewAny_university'  => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\University::class),
                'viewAny_user'        => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\User::class),
                'super_admin_check'   => $user->hasRole('super_admin'),
                'define_via_gate'     => config('filament-shield.super_admin.define_via_gate'),
            ];
        } catch (\Throwable $e) {
            $results['gate_checks']['error'] = $e->getMessage();
        }

        // فحص جداول الصلاحيات
        try {
            $results['permission_table_rows'] = [
                'roles_count'           => \Illuminate\Support\Facades\DB::table('roles')->count(),
                'permissions_count'     => \Illuminate\Support\Facades\DB::table('permissions')->count(),
                'model_has_roles_count' => \Illuminate\Support\Facades\DB::table('model_has_roles')->count(),
                'role_has_perms_count'  => \Illuminate\Support\Facades\DB::table('role_has_permissions')->count(),
                'user_roles'            => \Illuminate\Support\Facades\DB::table('model_has_roles')
                    ->where('model_id', $user->USER_IDENT)
                    ->pluck('role_id'),
            ];
        } catch (\Throwable $e) {
            $results['permission_table_rows']['error'] = $e->getMessage();
        }

        // فحص كل Resource وهل يظهر في القائمة
        $resources = [
            \App\Filament\Resources\Universities\UniversityResource::class,
            \App\Filament\Resources\Faculties\FacultyResource::class,
            \App\Filament\Resources\Programs\ProgramResource::class,
            \App\Filament\Resources\Users\UserResource::class,
            \App\Filament\Resources\Roles\RoleResource::class,
        ];

        foreach ($resources as $resource) {
            try {
                $results['resources'][$resource] = [
                    'canAccess'                => $resource::canAccess(),
                    'shouldRegisterNavigation' => $resource::shouldRegisterNavigation(),
                    'navigationGroup'          => $resource::getNavigationGroup(),
                ];
            } catch (\Throwable $e) {
                $results['errors'][$resource] = $e->getMessage();
            }
        }

        return response()->json($results);
    });

Route::get('/', function () {
    return view('welcome');
});

Route::get('/show_table', function() {
    return response()->json(Schema::connection('tenant')->getColumnListing('app_bill_ident_canceled'));
});

Route::get('/test-login-diag', function() {
    $results = [];

    // 1. Check storage permissions
    $logPath = storage_path('logs');
    $results['storage_logs_writable'] = is_writable($logPath);
    $results['storage_logs_path'] = $logPath;

    // 2. Check academic databases config
    $academicConfig = config('academic_years');
    $results['academic_config'] = $academicConfig;

    // 3. Check selected database
    $targetDb = request('db', config('academic_years.default_database', 'test_p_oas_db_2022'));
    $results['target_database'] = $targetDb;

    // 4. Test database connection
    try {
        config(['database.connections.tenant.database' => $targetDb]);
        config(['database.connections.mysql.database' => $targetDb]);
        DB::purge('tenant');
        DB::purge('mysql');
        DB::setDefaultConnection('tenant');

        $dbName = DB::connection('tenant')->select('SELECT DATABASE() as db')[0]->db ?? 'unknown';
        $results['connected_database'] = $dbName;
    } catch (\Throwable $e) {
        $results['database_connection_error'] = $e->getMessage();
        return response()->json($results, 500, ['Content-Type' => 'application/json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // 5. Check crucial tables
    $crucialTables = ['users', 'sessions', 'roles', 'permissions', 'model_has_roles', 'role_has_permissions', 'university'];
    $tablesStatus = [];
    foreach ($crucialTables as $tbl) {
        try {
            $has = Schema::connection('tenant')->hasTable($tbl);
            $count = $has ? DB::connection('tenant')->table($tbl)->count() : 0;
            $tablesStatus[$tbl] = ['exists' => $has, 'rows_count' => $count];
        } catch (\Throwable $e) {
            $tablesStatus[$tbl] = ['exists' => false, 'error' => $e->getMessage()];
        }
    }
    $results['tables_status'] = $tablesStatus;

    // 6. List all available roles in database
    try {
        if (Schema::connection('tenant')->hasTable('roles')) {
            $results['available_roles_in_db'] = DB::connection('tenant')->table('roles')->pluck('name')->toArray();
        }
    } catch (\Throwable $e) {
        $results['available_roles_error'] = $e->getMessage();
    }

    // 7. Check User record
    $testLogonId = request('user', 'ycit@gmail.com');
    $testPassword = request('pass', 'He-Ycit-321');

    $user = \App\Models\User::where('LOGON_ID', $testLogonId)->first();
    if ($user) {
        $results['user_found'] = [
            'USER_IDENT' => $user->USER_IDENT,
            'USER_NAME' => $user->USER_NAME,
            'LOGON_ID' => $user->LOGON_ID,
            'IS_IT_ENABLE' => $user->IS_IT_ENABLE,
            'UNID' => $user->UNID,
            'GROUP_IDENT' => $user->GROUP_IDENT,
            'stored_hash' => $user->LOGON_PASS,
        ];

        // 8. Check assigned roles & permissions for this user
        try {
            $directRoles = DB::connection('tenant')->table('model_has_roles')
                ->where('model_id', $user->USER_IDENT)
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->pluck('roles.name')
                ->toArray();

            $results['user_roles_direct_query'] = $directRoles;
            $results['user_roles_via_spatie'] = $user->getRoleNames()->toArray();
            $results['is_admin_check'] = $user->isAdmin();
            $results['has_super_admin_role'] = $user->hasRole('super_admin');
            $results['user_permissions_count'] = count($user->getAllPermissions());

            // Optional auto-assign super_admin if requested via ?assign_admin=1
            if (request()->has('assign_admin') || request()->has('assign_super_admin')) {
                // Ensure super_admin role exists
                $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
                if (! $user->hasRole('super_admin')) {
                    $user->assignRole($superAdminRole);
                    $results['action_taken'] = 'تم بنجاح إسناد دور super_admin للمستخدم ' . $user->LOGON_ID;
                    $results['user_roles_after_assign'] = $user->fresh()->getRoleNames()->toArray();
                } else {
                    $results['action_taken'] = 'المستخدم يمتلك دور super_admin بالفعل.';
                }
            }
        } catch (\Throwable $e) {
            $results['roles_permissions_check_error'] = $e->getMessage();
        }

        // 9. Test password validation via Provider
        $provider = Auth::getProvider();
        $results['provider_class'] = get_class($provider);

        $valid = $provider->validateCredentials($user, ['password' => $testPassword]);
        $results['password_validation_result'] = $valid;
        $results['tested_password'] = $testPassword;
        $results['post_validation_hash'] = $user->fresh()->LOGON_PASS;

        // 10. Test canAccessPanel
        try {
            $panel = \Filament\Facades\Filament::getCurrentPanel() ?? \Filament\Facades\Filament::getPanel('admin');
            $canAccess = $user->canAccessPanel($panel);
            $results['can_access_panel'] = $canAccess;
        } catch (\Throwable $e) {
            $results['can_access_panel_error'] = $e->getMessage();
        }
    } else {
        $results['user_found'] = false;
        $results['user_error'] = "User '{$testLogonId}' not found in database '{$dbName}'";
    }

    return response()->json($results, 200, ['Content-Type' => 'application/json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
});

Route::get('/admin/logout', function () {
    \Filament\Facades\Filament::auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/admin/login');
});

Route::get('/admin/high-school-certificates/{record}', function (\App\Models\HighSchoolDegreeBType $record) {
    // 1. The Global Scope (HasUniversityScope) automatically ensures the user can only fetch records for their university.
    
    // 2. Check if the user has the 'showWithCertificate' or 'approve' permission
    if (!auth()->user()->can('showWithCertificate', $record) && !auth()->user()->can('approve', $record)) {
        abort(403, 'عذراً، ليس لديك صلاحية لاستعراض هذه الشهادة.');
    }
    
    // 3. Construct the dynamic path
    $portalYear = PortalHelper::getActiveYear();
    $path = "uploads/p{$portalYear}/images/attachments/secondary/{$record->SEC_SCHOOL_CERTIFICATE}.jpg";
    
    $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));
    
    if (!$disk->exists($path)) {
        // If local file is missing, show a dummy certificate image for testing purposes
        return redirect('https://dummyimage.com/600x800/e2e8f0/475569.png&text=No+Certificate+Found');
    }
    
    // 4. Return the file
    return $disk->response($path);
})->middleware(['auth', 'web'])->name('high-school.certificate.download');

Route::get('/admin/applicant/{unid}/{applicant_ident}/receipt', [\App\Http\Controllers\ApplicantReceiptController::class, 'show'])
    ->middleware(['auth', 'web'])
    ->name('applicant.receipt');

Route::get('/admin/clearing-attachments/{unid}/{applicant_ident}/{type}', function ($unid, $applicant_ident, $type) {
    $record = \App\Models\ClearingApplicant::where('UNID', $unid)->where('APPLICANT_IDENT', $applicant_ident)->firstOrFail();

    // Check authorization
    if (!auth()->user()->can('showClearingAttachments', $record) && !auth()->user()->can('approve', $record)) {
        abort(403, 'عذراً، ليس لديك صلاحية لاستعراض هذا المرفق.');
    }

    $allowedTypes = ['grades', 'clearing', 'exceptions'];
    if (!in_array($type, $allowedTypes)) {
        abort(404);
    }

    $activeConnection = $record->getConnectionName() ?? config('database.default');
    $dbName = config("database.connections.{$activeConnection}.database");
    $baseDir = config("legacy_attachments.systems.{$dbName}", config("legacy_attachments.systems.{$activeConnection}", "uploads/{$activeConnection}"));

    $path = rtrim($baseDir, '/') . "/images/attachments/{$type}/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";

    $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));

    if (!$disk->exists($path)) {
        abort(404, 'الملف غير موجود');
    }

    return $disk->response($path);
})->middleware(['auth', 'web'])->name('clearing.attachment.download');
