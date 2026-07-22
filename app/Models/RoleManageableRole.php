<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Class RoleManageableRole
 *
 * @property int $id
 * @property int $role_id
 * @property int $manageable_role_id
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Role $role
 *
 * @package App\Models
 */
class RoleManageableRole extends Model
{
    protected $table = 'role_manageable_roles';

    protected $casts = [
        'role_id' => 'int',
        'manageable_role_id' => 'int',
        'created_by' => 'int'
    ];

    protected $fillable = [
        'role_id',
        'manageable_role_id',
        'created_by'
    ];

    protected static function booted(): void
    {
        static::creating(function ($record) {
            $record->created_by = Auth::id();
        });
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
