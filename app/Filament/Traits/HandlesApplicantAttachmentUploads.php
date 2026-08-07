<?php

namespace App\Filament\Traits;

use App\Helpers\PortalHelper;
use App\Models\ApplicantAttachment;
use Illuminate\Support\Facades\Storage;

trait HandlesApplicantAttachmentUploads
{
    protected function mutateApplicantAttachmentData(array $data): array
    {
        unset(
            $data['clearing_attachment_grades'],
            $data['clearing_attachment_form'],
            $data['clearing_attachment_exception'],
            $data['secondary_certificate']
        );

        return $data;
    }

    protected function syncApplicantAttachments(): void
    {
        $record = $this->record;
        if (!$record) {
            return;
        }

        $disk = Storage::disk(config('legacy_attachments.disk', 'public'));
        $portalPrefix = PortalHelper::getPortalPrefix();

        $clearingMap = [
            3 => "uploads/{$portalPrefix}/images/attachments/grades/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf",
            4 => "uploads/{$portalPrefix}/images/attachments/clearing/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf",
            5 => "uploads/{$portalPrefix}/images/attachments/exceptions/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf",
        ];

        foreach ($clearingMap as $ident => $path) {
            if ($disk->exists($path)) {
                ApplicantAttachment::updateOrCreate(
                    ['UNID' => $record->UNID, 'APPLICANT_IDENT' => $record->APPLICANT_IDENT, 'ATTACH_IDENT' => $ident],
                    []
                );
            }
        }

        // Secondary certificate (ATTACH_IDENT = 2)
        $secJpg = "uploads/{$portalPrefix}/images/attachments/secondary/{$record->UNID}-{$record->APPLICANT_IDENT}.jpg";
        $hasSec = $disk->exists($secJpg);

        if (!$hasSec) {
            $degreeB = \App\Models\HighSchoolDegreeBType::withoutGlobalScopes()
                ->where('UNID', $record->UNID)
                ->where('SEC_SCHOOL_SEATNO', $record->SEC_SCHOOL_SEATNO)
                ->where('SEC_SCHOOL_YEAR', $record->SEC_SCHOOL_YEAR)
                ->first();

            if (!$degreeB && !empty($record->SEC_SCHOOL_SEATNO) && !empty($record->SEC_SCHOOL_YEAR)) {
                $degreeB = \App\Models\HighSchoolDegreeBType::withoutGlobalScopes()
                    ->where('SEC_SCHOOL_SEATNO', $record->SEC_SCHOOL_SEATNO)
                    ->where('SEC_SCHOOL_YEAR', $record->SEC_SCHOOL_YEAR)
                    ->first();
            }

            if (!$degreeB && !empty($record->SEC_SCHOOL_SEATNO)) {
                $degreeB = \App\Models\HighSchoolDegreeBType::withoutGlobalScopes()
                    ->where('SEC_SCHOOL_SEATNO', $record->SEC_SCHOOL_SEATNO)
                    ->first();
            }

            if ($degreeB && $degreeB->SEC_SCHOOL_CERTIFICATE) {
                $cert = basename($degreeB->SEC_SCHOOL_CERTIFICATE, '.jpg');
                $bJpg = "uploads/{$portalPrefix}/images/attachments/secondary/{$cert}.jpg";
                if ($disk->exists($bJpg)) {
                    $hasSec = true;
                }
            }
        }

        if ($hasSec) {
            ApplicantAttachment::updateOrCreate(
                ['UNID' => $record->UNID, 'APPLICANT_IDENT' => $record->APPLICANT_IDENT, 'ATTACH_IDENT' => 2],
                []
            );
        }
    }
}
