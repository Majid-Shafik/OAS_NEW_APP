<?php

namespace App\Models;

use App\Models\Scopes\UniversityScope;
use App\Traits\HasUniversityScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class AttachmentsClearing extends Model
{
    // Removing HasUniversityScope since this table might be global or not depend on UNID?
    // Wait, the legacy attachment definition might be global or per university. Let's assume global.
    
    protected $table = 'attachments_clearing';

    protected $primaryKey = 'ATTACH_IDENT';
    
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'ATTACH_IDENT',
        'ATTACH_NAME',
        'FORCE_RESIZE',
        'ATTACH_HEIGHT',
        'ATTACH_WIDTH',
        'ATTACH_SMALLEST_SIZE_KBYTE',
        'ATTACH_BIGGEST_SIZE_KBYTE',
        'PARENT_FOLDER',
    ];

    /**
     * Get the dynamically resolved upload directory based on the active connection/university.
     * Removes '../' and prefixes with the active database upload path.
     */
    public function getUploadDirectoryAttribute(): string
    {
        $activeDb = session('tenant_database', config('database.connections.tenant.database', config('database.default')));
        
        $baseDir = config("legacy_attachments.systems.{$activeDb}");
        
        if (!$baseDir) {
            if (preg_match('/(20\d{2})/', (string) $activeDb, $matches)) {
                $baseDir = "uploads/p{$matches[1]}";
            } else {
                $baseDir = "uploads/{$activeDb}";
            }
        }
        
        $cleanParentFolder = str_replace(['../', './'], '', $this->PARENT_FOLDER ?? '');
        
        return rtrim($baseDir, '/') . '/' . ltrim($cleanParentFolder, '/');
    }
}
