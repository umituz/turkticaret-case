<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Base Model class providing UUID primary keys and common functionality.
 * 
 * Abstract base class for all application models that require UUID primary keys,
 * soft delete functionality, and factory support. Provides consistent UUID handling,
 * route model binding configuration, and common model traits across the application.
 *
 * @property string $uuid The model's UUID primary key
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft deletion timestamp
 * 
 * @package App\Models\Base
 */
abstract class BaseUuidModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Get the route key name for route model binding.
     *
     * @return string The name of the route key (uuid)
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
