<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** Stores uploads on the public disk under a fixed folder with random filenames. */
final class Uploads
{
    public const DIRS = ['branding', 'categories', 'products'];

    public static function replace(?string $oldPath, UploadedFile $file, string $dir): string
    {
        $path = $file->store($dir, 'public');
        self::remove($oldPath, $dir);

        return $path;
    }

    /** Deletes only files this app stored in $dir, so a stray setting can never delete unrelated files. */
    public static function remove(?string $path, string $dir): void
    {
        if ($path && str_starts_with($path, $dir.'/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
