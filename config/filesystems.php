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
            // Deliberately NOT served (no 'url', no 'serve' => true): this
            // disk holds paid release files (ReleaseLibrary) and database
            // backup zips (BackupService), both strictly private — every
            // real usage reads them server-side via ->path(), never a
            // public URL. Releases are only ever handed out through
            // DownloadController's signed, entitlement-checked route.
            // 'serve' => true here previously defaulted to /storage (no
            // 'url' was set), and nothing else claimed that URI before
            // this commit — so this was live in production: both classes
            // of private file were reachable at a guessable /storage/{path}
            // URL with no entitlement check, since day one of this disk's
            // 'serve' flag being set.
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
            // Laravel's built-in stand-in for the public/storage symlink
            // (Illuminate\Filesystem\FilesystemServiceProvider::serveFiles())
            // — registers a GET /storage/{path} route that serves this disk
            // directly. Needed because `php artisan storage:link` cannot
            // succeed on this Hostinger plan: symlink()/exec() are both
            // disabled on its shared PHP stack (see HOSTINGER_DEPLOYMENT.md),
            // which otherwise left every Media::url()/thumbnailUrl() link
            // 404ing in production — every app icon, feature graphic, and
            // screenshot site-wide.
            'serve' => true,
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

];
