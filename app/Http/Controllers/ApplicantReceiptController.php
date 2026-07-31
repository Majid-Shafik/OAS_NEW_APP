<?php

namespace App\Http\Controllers;

use App\Helpers\PortalHelper;
use App\Models\Applicant;
use App\Models\ApplicationGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicantReceiptController extends Controller
{
    public function show($unid, $applicantIdent)
    {
        $applicant = Applicant::with(['university'])
            ->where('UNID', $unid)
            ->where('APPLICANT_IDENT', $applicantIdent)
            ->firstOrFail();

        // Ensure user has access
        $userUnid = auth()->user()->UNID;
        if ($userUnid == 0) {
            $selectedUnid = session('selected_unid', 0);
            if ($selectedUnid == 0 || $selectedUnid != $unid) {
                abort(403, 'غير مصرح لك باستعراض حافظة هذه الجامعة.');
            }
        } elseif ($userUnid != $unid) {
            abort(403, 'غير مصرح لك باستعراض حافظة هذه الجامعة.');
        }

        $appGroups = ApplicationGroup::with([
            'faculty', 
            'studyType', 
            'offerGroup', 
            'applications.faculty', 
            'applications.program', 
            'applications.studyType'
        ])
            ->where('UNID', $unid)
            ->where('APPLICANT_IDENT', $applicantIdent)
            ->where(function ($q) {
                $q->whereNull('PAYMENT')
                  ->orWhere('PAYMENT', 0);
            })
            ->get();

        $portalYear = PortalHelper::getActiveYear();
        
        // Base64 encode the logo to avoid public symlink and 403 Forbidden issues
        $physicalPath = storage_path("app/public/uploads/p{$portalYear}/images/{$unid}.png");
        $logoPath = '';
        if (file_exists($physicalPath)) {
            $mime = mime_content_type($physicalPath) ?: 'image/png';
            $data = file_get_contents($physicalPath);
            $logoPath = 'data:' . $mime . ';base64,' . base64_encode($data);
        }
        return view('receipts.applicant_fees', compact('applicant', 'appGroups', 'logoPath'));
    }
}
