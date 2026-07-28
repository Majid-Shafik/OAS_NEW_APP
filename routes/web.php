<?php

use App\Helpers\PortalHelper;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/show_table', function() {
    return response()->json(Schema::connection('tenant')->getColumnListing('app_bill_ident_canceled'));
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
