<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for listing products with filters.
 * 
 * Handles validation of product listing parameters including pagination,
 * category filtering, price range filtering, and search functionality.
 * Provides flexible querying options for product browsing.
 *
 * @package App\Http\Requests\Product
 */
class ProductListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always returns true for public product listing
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string> Array of validation rules
     */
    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'limit' => 'nullable|integer|min:1|max:100', // Backward compatibility
            'category_uuid' => 'nullable|uuid|exists:categories,uuid',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0|gte:min_price',
            'search' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get the validated filters for product listing.
     *
     * @return array<string, mixed> Validated filter parameters
     */
    public function filters(): array
    {
        return $this->validated();
    }
}