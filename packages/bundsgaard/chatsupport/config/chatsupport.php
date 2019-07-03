<?php

return [
    'listen' => [
        'Bundsgaard\ChatSupport\Events\OpenEvent' => [],
        'Bundsgaard\ChatSupport\Events\MessageEvent' => [
            'Bundsgaard\ChatSupport\Listeners\SessionConnectListener',
            'Bundsgaard\ChatSupport\Listeners\SessionRoomListener',
            'Bundsgaard\ChatSupport\Listeners\AssignListener',
            'Bundsgaard\ChatSupport\Listeners\UnassignListener',
            'Bundsgaard\ChatSupport\Listeners\TypingListener',
            'Bundsgaard\ChatSupport\Listeners\MessageListener',
        ],
        'Bundsgaard\ChatSupport\Events\CloseEvent' => [
            'Bundsgaard\ChatSupport\Listeners\CloseListener',
        ],
        'Bundsgaard\ChatSupport\Events\ErrorEvent' => [],
    ],

    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql')
        ],
    ],
];
