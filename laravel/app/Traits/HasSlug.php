<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
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