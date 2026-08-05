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

// منع خطأ MethodNotAllowedHttpException عند قيام المتصفح بطلب مسار التحديث بـ GET (كإعادة التوجيه أو التراجع)
Route::get('/livewire{any}/update', function () {
    return redirect('/admin/login');
})->where('any', '.*');



    // مسار لتفريغ الكاش بالكامل
    Route::get('/clear-cache', function () {
        if (app()->environment('production')) {
            abort(403, 'غير مصرح في بيئة الإنتاج.');
        }
        $commands = [
            'route:clear', 
            'config:clear', 
            'cache:clear', 
            'view:clear', 
            'permission:cache-reset',
            'filament:clear-cached-components'
        ];
        $output = [];
        foreach ($commands as $cmd) {
            \Illuminate\Support\Facades\Artisan::call($cmd);
            $output[$cmd] = trim(\Illuminate\Support\Facades\Artisan::output()) ?: 'Done';
        }

        return response()->json([
            'msg'    => 'تم مسح الكاش بنجاح.',
            'output' => $output,
        ]);
    });



    // مسار لعمل كاش (تسريع النظام)
    Route::get('/optimize-system', function () {
        if (app()->environment('production')) {
            abort(403, 'غير مصرح في بيئة الإنتاج.');
        }
        $commands = [
            'config:cache', 
            'route:cache', 
            'view:cache', 
            'filament:cache-components',
            'icons:cache'
        ];
        $output = [];
        foreach ($commands as $cmd) {
            try {
                \Illuminate\Support\Facades\Artisan::call($cmd);
                $output[$cmd] = trim(\Illuminate\Support\Facades\Artisan::output()) ?: 'Done';
            } catch (\Exception $e) {
                $output[$cmd] = 'Error: ' . $e->getMessage();
            }
        }

        return response()->json([
            'msg'    => 'تم عمل كاش وتسريع النظام بنجاح.',
            'output' => $output,
        ]);
    });



Route::get('/', function () {
    return view('welcome');
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
    $portalPrefix = PortalHelper::getPortalPrefix();
    $path = "uploads/{$portalPrefix}/images/attachments/secondary/{$record->SEC_SCHOOL_CERTIFICATE}.jpg";
    
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

    $portalPrefix = PortalHelper::getPortalPrefix();
    $path = "uploads/{$portalPrefix}/images/attachments/{$type}/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";

    $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));

    if (!$disk->exists($path)) {
        abort(404, 'الملف غير موجود');
    }

    return $disk->response($path);
})->middleware(['auth', 'web'])->name('clearing.attachment.download');
