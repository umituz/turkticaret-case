<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class RouteHelper
{
    /**
     * Recursively include all PHP files from a directory
     *
     * @param string $directory
     * @return void
     */
    public static function includeRoutesFromDirectory(string $directory): void
    {
        if (!File::isDirectory($directory)) {
            return;
        }

        $files = File::allFiles($directory);

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                require $file->getPathname();
            }
        }
    }
}
