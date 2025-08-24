<?php

namespace App\Http\Resources\Category;

use App\Http\Resources\Base\BaseResource;
use Illuminate\Http\Request;

/**
 * API Resource for transforming Category data.
 * 
 * Handles the transformation of Category model instances into standardized
 * JSON API responses. Includes category details, metadata, and hierarchical
 * information for API consumption and frontend rendering.
 *
 * @package App\Http\Resources\Category
 */
class CategoryResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the category resource
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
