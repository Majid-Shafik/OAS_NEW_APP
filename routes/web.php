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

Route::get('/admin/high-school-certificates/{record}', function ($recordId) {
    $record = \App\Models\HighSchoolDegreeBType::withoutGlobalScopes()->findOrFail($recordId);
    $user = auth()->user();

    // 1. University isolation check: A user from University X cannot view records of University Y
    if ($user->UNID != 0 && $record->UNID != 0 && (int)$record->UNID !== (int)$user->UNID) {
        abort(403, 'عذراً، لا يحق لك الاطلاع على مرفقات تتبع جامعة أخرى.');
    }
    $selectedUnid = (int)session('selected_unid', 0);
    if ($user->UNID == 0 && $selectedUnid !== 0 && $record->UNID != 0 && (int)$record->UNID !== $selectedUnid) {
        abort(403, 'عذراً، السجل لا يتبع الجامعة المحددة حالياً.');
    }

    // 2. Permission check
    if (!$user->can('showWithCertificate', $record) && !$user->can('approve', $record) && !$user->can('view', $record) && !$user->can('update', $record)) {
        abort(403, 'عذراً، ليس لديك صلاحية لاستعراض هذه الشهادة.');
    }
    
    // Construct the dynamic path
    $portalPrefix = PortalHelper::getPortalPrefix();
    $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));
    $cert = basename($record->SEC_SCHOOL_CERTIFICATE, '.jpg');
    $jpgPath = "uploads/{$portalPrefix}/images/attachments/secondary/{$cert}.jpg";

    if (!$disk->exists($jpgPath)) {
        $candidatePaths = [
            "uploads/images/attachments/secondary/{$cert}.jpg",
            "uploads/secondary/{$cert}.jpg",
        ];
        foreach ($candidatePaths as $cp) {
            if ($disk->exists($cp)) {
                $jpgPath = $cp;
                break;
            }
        }
    }

    if (!$disk->exists($jpgPath)) {
        // If local file is missing, show a dummy certificate image for testing purposes
        return redirect('https://dummyimage.com/600x800/e2e8f0/475569.png&text=No+Certificate+Found');
    }
    
    // Return the file
    return $disk->response($jpgPath);
})->middleware(['auth', 'web'])->name('high-school.certificate.download');

Route::get('/admin/applicant/{unid}/{applicant_ident}/receipt', [\App\Http\Controllers\ApplicantReceiptController::class, 'show'])
    ->middleware(['auth', 'web'])
    ->name('applicant.receipt');

Route::get('/admin/clearing-attachments/{unid}/{applicant_ident}/{type}', function ($unid, $applicant_ident, $type) {
    $user = auth()->user();

    // 1. University isolation check: A user from University X cannot view attachments of University Y
    if ($user->UNID != 0 && (int)$unid !== (int)$user->UNID) {
        abort(403, 'عذراً، لا يحق لك الاطلاع على مرفقات تتبع جامعة أخرى.');
    }
    $selectedUnid = (int)session('selected_unid', 0);
    if ($user->UNID == 0 && $selectedUnid !== 0 && (int)$unid !== $selectedUnid) {
        abort(403, 'عذراً، السجل لا يتبع الجامعة المحددة حالياً.');
    }

    $record = \App\Models\Applicant::withoutGlobalScopes()->where('UNID', $unid)->where('APPLICANT_IDENT', $applicant_ident)->firstOrFail();

    // 2. Permission check
    if (!$user->can('showClearingAttachments', $record) && !$user->can('approve', $record) && !$user->can('view', $record) && !$user->can('update', $record)) {
        abort(403, 'عذراً، ليس لديك صلاحية لاستعراض هذا المرفق.');
    }

    $allowedTypes = ['grades', 'clearing', 'exceptions', 'secondary'];
    if (!in_array($type, $allowedTypes)) {
        abort(404);
    }

    $portalPrefix = PortalHelper::getPortalPrefix();
    $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));

    if ($type === 'secondary') {
        $path = "uploads/{$portalPrefix}/images/attachments/secondary/{$record->UNID}-{$record->APPLICANT_IDENT}.jpg";
        if (!$disk->exists($path)) {
            // Also check HighSchoolDegreeBType
            $degreeB = \App\Models\HighSchoolDegreeBType::withoutGlobalScopes()->where('UNID', $record->UNID)
                ->where('SEC_SCHOOL_SEATNO', $record->SEC_SCHOOL_SEATNO)
                ->where('SEC_SCHOOL_YEAR', $record->SEC_SCHOOL_YEAR)
                ->first();

            if (!$degreeB && !empty($record->SEC_SCHOOL_SEATNO) && !empty($record->SEC_SCHOOL_YEAR)) {
                $degreeB = \App\Models\HighSchoolDegreeBType::withoutGlobalScopes()->where('SEC_SCHOOL_SEATNO', $record->SEC_SCHOOL_SEATNO)
                    ->where('SEC_SCHOOL_YEAR', $record->SEC_SCHOOL_YEAR)
                    ->first();
            }

            if (!$degreeB && !empty($record->SEC_SCHOOL_SEATNO)) {
                $degreeB = \App\Models\HighSchoolDegreeBType::withoutGlobalScopes()->where('SEC_SCHOOL_SEATNO', $record->SEC_SCHOOL_SEATNO)
                    ->first();
            }

            if ($degreeB && $degreeB->SEC_SCHOOL_CERTIFICATE) {
                $cert = basename($degreeB->SEC_SCHOOL_CERTIFICATE, '.jpg');
                $path = "uploads/{$portalPrefix}/images/attachments/secondary/{$cert}.jpg";
            }
        }
    } else {
        $path = "uploads/{$portalPrefix}/images/attachments/{$type}/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";
    }

    if (!$disk->exists($path)) {
        abort(404, 'المرفق غير موجود على الخادم.');
    }

    return $disk->response($path);
})->middleware(['auth', 'web'])->name('clearing.attachment.download');

Route::post('/admin/clearing-attachments/{unid}/{applicant_ident}/{type}/delete', function ($unid, $applicant_ident, $type) {
    $user = auth()->user();

    // 1. University isolation check
    if ($user->UNID != 0 && (int)$unid !== (int)$user->UNID) {
        abort(403, 'عذراً، لا يحق لك حذف مرفقات تتبع جامعة أخرى.');
    }
    $selectedUnid = (int)session('selected_unid', 0);
    if ($user->UNID == 0 && $selectedUnid !== 0 && (int)$unid !== $selectedUnid) {
        abort(403, 'عذراً، السجل لا يتبع الجامعة المحددة حالياً.');
    }

    $record = \App\Models\Applicant::withoutGlobalScopes()->where('UNID', $unid)->where('APPLICANT_IDENT', $applicant_ident)->firstOrFail();

    // 2. Permission check
    if (!$user->can('update', $record)) {
        abort(403, 'عذراً، ليس لديك صلاحية لحذف هذا المرفق.');
    }

    $allowedTypes = ['grades', 'clearing', 'exceptions', 'secondary'];
    if (!in_array($type, $allowedTypes)) {
        abort(404);
    }

    $portalPrefix = PortalHelper::getPortalPrefix();
    $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));

    $attachIdentMap = [
        'secondary' => 2,
        'grades' => 3,
        'clearing' => 4,
        'exceptions' => 5,
    ];

    if ($type === 'secondary') {
        $extensions = ['jpg'];
        foreach ($extensions as $ext) {
            $filePath = "uploads/{$portalPrefix}/images/attachments/secondary/{$record->UNID}-{$record->APPLICANT_IDENT}.{$ext}";
            if ($disk->exists($filePath)) {
                $disk->delete($filePath);
            }
        }
    } else {
        $filePath = "uploads/{$portalPrefix}/images/attachments/{$type}/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";
        if ($disk->exists($filePath)) {
            $disk->delete($filePath);
        }
    }

    if (isset($attachIdentMap[$type])) {
        \App\Models\ApplicantAttachment::where('UNID', $record->UNID)
            ->where('APPLICANT_IDENT', $record->APPLICANT_IDENT)
            ->where('ATTACH_IDENT', $attachIdentMap[$type])
            ->delete();
    }

    \Filament\Notifications\Notification::make()
        ->success()
        ->title('تم حذف المرفق بنجاح')
        ->send();

    return back();
})->middleware(['auth', 'web'])->name('clearing.attachment.delete');

// Fallback to safely serve public storage files with strict authentication & university isolation
Route::get('/storage/{path}', function ($path) {
    $user = auth()->user();
    if (!$user) {
        abort(403, 'عذراً، يجب تسجيل الدخول للوصول إلى هذا الملف.');
    }

    // If accessing applicant attachments, enforce strict university isolation and permission checks
    if (str_contains($path, 'attachments/')) {
        $filename = basename($path);
        if (preg_match('/^(\d+)-(\d+)\.(jpg|pdf)$/i', $filename, $matches)) {
            $fileUnid = (int)$matches[1];
            $applicantIdent = (int)$matches[2];

            // 1. University isolation check
            if ($user->UNID != 0 && $fileUnid !== (int)$user->UNID) {
                abort(403, 'عذراً، لا يحق لك الاطلاع على مرفقات تتبع جامعة أخرى.');
            }
            $selectedUnid = (int)session('selected_unid', 0);
            if ($user->UNID == 0 && $selectedUnid !== 0 && $fileUnid !== $selectedUnid) {
                abort(403, 'عذراً، السجل لا يتبع الجامعة المحددة حالياً.');
            }

            // 2. Permission check
            $record = \App\Models\Applicant::withoutGlobalScopes()->where('UNID', $fileUnid)->where('APPLICANT_IDENT', $applicantIdent)->first();
            if ($record) {
                if (!$user->can('showClearingAttachments', $record) && !$user->can('approve', $record) && !$user->can('view', $record) && !$user->can('update', $record)) {
                    abort(403, 'عذراً، ليس لديك صلاحية لاستعراض هذا المرفق.');
                }
            }
        }
    }

    $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));
    if ($disk->exists($path)) {
        return $disk->response($path);
    }
    abort(404);
})->where('path', '.*')->middleware(['auth', 'web'])->name('storage.fallback');
