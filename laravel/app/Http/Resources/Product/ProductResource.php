<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Base\BaseResource;
use App\Http\Resources\Category\CategoryResource;
use Illuminate\Http\Request;

class ProductResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => $this->price,
            'stock_quantity' => $this->stock_quantity,
            'image_path' => $this->image_path,
            'is_active' => $this->is_active,
            'category_uuid' => $this->category_uuid,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}