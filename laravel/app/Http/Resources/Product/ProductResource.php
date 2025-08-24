<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Base\BaseResource;
use App\Http\Resources\Category\CategoryResource;
use Illuminate\Http\Request;

/**
 * API Resource for transforming Product data.
 * 
 * Handles the transformation of Product model instances into standardized
 * JSON API responses. Includes product details, pricing, inventory information,
 * media handling, category relationships, and formatting for e-commerce API consumption.
 *
 * @package App\Http\Resources\Product
 */
class ProductResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the product resource with category and media
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => $this->price,
            'stock_quantity' => $this->stock_quantity,
            'image_path' => $this->getFirstMediaUrl('images'),
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'category_uuid' => $this->category_uuid,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
