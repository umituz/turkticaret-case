<?php

namespace App\Models\Authority;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Role model extending Spatie's Role model with UUID support.
 * 
 * This model handles application roles using Laravel's Spatie Permission package.
 * It provides role-based access control with UUID primary keys and soft delete functionality.
 * Roles can be assigned to users and associated with permissions to control access throughout the application.
 *
 * @property string $uuid Role unique identifier
 * @property string $name Role name (unique identifier for the role)
 * @property string $guard_name Guard name for the role
 * @property string|null $description Human-readable description of the role
 * @property \Carbon\Carbon $created_at Creation timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft deletion timestamp
 * 
 * @package App\Models\Authority
 */
class Role extends SpatieRole
{
    use HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'guard_name',
        'description',
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
