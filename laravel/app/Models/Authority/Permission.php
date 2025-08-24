<?php

namespace App\Models\Authority;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Permission model extending Spatie's Permission model with UUID support.
 * 
 * This model handles application permissions using Laravel's Spatie Permission package.
 * It provides role-based access control with UUID primary keys and soft delete functionality.
 * Permissions can be grouped and associated with roles to control user access throughout the application.
 *
 * @property string $uuid Permission unique identifier
 * @property string $name Permission name (unique identifier for the permission)
 * @property string $guard_name Guard name for the permission
 * @property string|null $description Human-readable description of the permission
 * @property string|null $group Permission group for organizational purposes
 * @property \Carbon\Carbon $created_at Creation timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft deletion timestamp
 * 
 * @package App\Models\Authority
 */
class Permission extends SpatiePermission
{
    use HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'group',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the route key name for the model.
     *
     * @return string The route key name (uuid)
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
