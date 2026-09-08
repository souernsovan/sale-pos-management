<?php

namespace App\Http\Controllers;

use App\Support\Uploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves uploaded files (product images, shop logo, site icon, profile
 * photos) through the app itself — used when UPLOADS_DISK=public_direct,
 * for hosts where the web server won't reliably serve files PHP wrote
 * directly into public/storage. See App\Support\Uploads::url().
 */
class UploadController extends Controller
{
    public function show(string $path): StreamedResponse
    {
        $disk = Uploads::disk();

        abort_unless($disk->exists($path), 404);

        return $disk->response($path);
    }
}
