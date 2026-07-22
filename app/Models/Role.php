<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Class Role
 *
 * @property int $id
 * @property string $name
 * @property string|null $label
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|ModelHasRole[] $model_has_roles
 * @property Collection|PlanStageTemplate[] $plan_stage_templates
 * @property Collection|PlanStage[] $plan_stages
 * @property Collection|Permission[] $permissions
 *
 * @package App\Models
 */
class Role extends SpatieRole
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'label',
        'guard_name'
    ];

    public static function getLabel(): string
    {
        return __('filament-shield::filament-shield.resource.label.role');
    }

    /**
     * الأدوار التي يمكن لهذا الدور إدارتها
     */
    public function manageableRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_manageable_roles',
            'role_id',  // هذا الدور
            'manageable_role_id'  // الدور الذي يمكن إدارته
        );
    }

    /**
     * الأدوار التي يمكن أن يديرها هذا الدور (inverse)
     */
    public function managedByRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_manageable_roles',
            'manageable_role_id',  // الدور الذي يمكن إدارته
            'role_id'  // الدور الذي يديره
        );
    }

    /**
     * النطاقات الخاصة بهذا الدور (فرع / وحدة)
     */

}
