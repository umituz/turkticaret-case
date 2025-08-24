<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * HasSlug trait for generating unique URL-friendly slugs.
 * 
 * Provides functionality to generate unique slugs based on model names,
 * ensuring no duplicate slugs exist in the database. Supports slug updating
 * by excluding the current model's slug during uniqueness checks.
 *
 * @package App\Traits
 */
trait HasSlug
{
    /**
     * Generate a unique slug based on the provided name.
     * 
     * Creates a URL-friendly slug from the given name and ensures uniqueness
     * by appending a numeric suffix if conflicts exist. When updating an existing
     * model, the current slug is excluded from uniqueness checks.
     *
     * @param string $name The name to generate a slug from
     * @param string|null $currentSlug Current slug to exclude from uniqueness check (for updates)
     * @return string The generated unique slug
     */
    public function generateUniqueSlug(string $name, ?string $currentSlug = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = $this->where('slug', $slug);
        
        if ($currentSlug) {
            $query->where('slug', '!=', $currentSlug);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            
            $query = $this->where('slug', $slug);
            if ($currentSlug) {
                $query->where('slug', '!=', $currentSlug);
            }
        }

        return $slug;
    }
}