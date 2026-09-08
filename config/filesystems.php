<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            // A relative path, not an absolute env('APP_URL') one — this app gets
            // accessed through different ports/hosts across environments, and an
            // absolute URL baked from APP_URL breaks (404) whenever that doesn't
            // match how the browser is actually reaching the app.
            'url' => '/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // Same public URL convention as the 'public' disk above, but writes
        // straight into public/storage instead of storage/app/public — no
        // `php artisan storage:link` symlink required. For shared hosts with
        // no terminal access and/or symlink() disabled (e.g. InfinityFree):
        // set UPLOADS_DISK=public_direct in .env to use this instead.
        'public_direct' => [
            'driver' => 'local',
            'root' => public_path('storage'),
            'url' => '/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Uploads Disk
    |--------------------------------------------------------------------------
    |
    | Which disk (from above) product images, the shop logo, the site icon,
    | and profile photos are stored on. Defaults to "public" (requires
    | `php artisan storage:link`). Set UPLOADS_DISK=public_direct in .env on
    | hosts where that command can't be run and/or symlink() is disabled.
    |
    */

    'uploads_disk' => env('UPLOADS_DISK', 'public'),

];
