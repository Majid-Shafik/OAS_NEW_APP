<?php

namespace App\Models;

use App\Enums\RequestUpdateType;
use App\Traits\HasUniversityScope;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RequestAdjustOffering extends Model
{
    use Compoships, HasFactory, HasUniversityScope;

    protected $table = 'request_adjust_offerings';

    protected $primaryKey = 'REQUEST_ID';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'TYPE_UPDATE' => RequestUpdateType::class,
    ];

    public function university()
    {
        return $this->belongsTo(University::class, 'UNID', 'UNID');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, ['UNID', 'FACULTY_IDENT'], ['UNID', 'FACULTY_IDENT']);
    }

    public function program()
    {
        return $this->belongsTo(Program::class, ['UNID', 'FACULTY_IDENT', 'PROGRAM_IDENT'], ['UNID', 'FACULTY_IDENT', 'PROGRAM_IDENT']);
    }

    public function offering()
    {
        return $this->belongsTo(Offering::class, 'OFFERING_IDENT', 'OFFERING_IDENT');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'ADD_BY', 'USER_IDENT');
    }

    public function runBy()
    {
        return $this->belongsTo(User::class, 'RUN_BY', 'USER_IDENT');
    }

    protected function getBaseAttachmentPath(): string
    {
        $dbName = DB::connection()->getDatabaseName();

        return config("legacy_attachments.systems.{$dbName}", "uploads/{$dbName}").'/uploads_pdf';
    }

    public function getUnAttachmentPath(): string
    {
        return Storage::disk(config('legacy_attachments.disk', 'public'))->path($this->getBaseAttachmentPath()."/un/req_{$this->REQUEST_ID}.pdf");
    }

    public function getMinistryAttachmentPath(): string
    {
        return Storage::disk(config('legacy_attachments.disk', 'public'))->path($this->getBaseAttachmentPath()."/ministry/req_{$this->REQUEST_ID}.pdf");
    }

    public function getUnAttachmentUrl(): string
    {
        return Storage::disk(config('legacy_attachments.disk', 'public'))->url($this->getBaseAttachmentPath()."/un/req_{$this->REQUEST_ID}.pdf");
    }

    public function getMinistryAttachmentUrl(): string
    {
        return Storage::disk(config('legacy_attachments.disk', 'public'))->url($this->getBaseAttachmentPath()."/ministry/req_{$this->REQUEST_ID}.pdf");
    }
}
