<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. A "local" driver, as well as a variety of cloud
    | based drivers are available for your choosing. Just store away!
    |
    | Supported: "local", "ftp", "s3", "rackspace"
    |
    */
    'default' => 'local',
    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */
    'cloud' => 's3',
    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    */
    'disks' => [
        'base' => [
            'driver' => 'local',
            'root' => base_path()
        ],
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],
        'framework-cache' => [
            'driver' => 'local',
            'root' => storage_path('framework/cache'),
        ],
        'framework-sessions' => [
            'driver' => 'local',
            'root' => storage_path('framework/sessions'),
        ],
        'framework-views' => [
            'driver' => 'local',
            'root' => storage_path('framework/views'),
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'visibility' => 'public',
        ],
        's3' => [
            'driver' => 's3',
            'key' => 'your-key',
            'secret' => 'your-secret',
            'region' => 'your-region',
            'bucket' => 'your-bucket',
        ],
        'app' => [
            'driver' => 'local',
            'root' => app_path(),
        ],
        // themes & plugins
        'themes' => [
            'driver' => 'local',
            'root' => public_path('themes')
        ],

        'themesbase' => [
            'driver' => 'local',
            'root' => base_path('Themes')
        ],

        'plugins' => [
            'driver' => 'local',
            'root' => app_path('Modules')
        ],

        'modules' => [
            'driver' => 'local',
            'root' => base_path('Modules')
        ],

        // uploads
        'files' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'path' => '/uploads'
        ],
        // adaptcms
        'cdn' => [
            'driver' => 's3',
        ]
    ]
];
