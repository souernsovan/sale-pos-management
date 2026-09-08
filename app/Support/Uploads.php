<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * The disk used for user-uploaded public files (product images, shop logo,
 * site icon, profile photos). See config/filesystems.php's "uploads_disk" —
 * defaults to the "public" disk, switchable via UPLOADS_DISK in .env for
 * hosts that can't run `storage:link` / have symlink() disabled.
 */
class Uploads
{
    public static function disk(): Filesystem
    {
        return Storage::disk(config('filesystems.uploads_disk', 'public'));
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        // On "public_direct" (hosts where the web server won't reliably
        // serve files PHP itself wrote into public/storage — seen on
        // InfinityFree, likely a permission/ownership mismatch between the
        // PHP process and the static file handler) route the request
        // through the app instead of linking straight to the file, so PHP
        // reads and streams it itself rather than relying on the web
        // server to serve it directly.
        if (config('filesystems.uploads_disk') === 'public_direct') {
            return route('uploads.show', ['path' => $path]);
        }

        return self::disk()->url($path);
    }
}
