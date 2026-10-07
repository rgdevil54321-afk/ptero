<?php

return [

    'default_serializer' => League\Fractal\Serializer\JsonApiSerializer::class,


    'auto_includes' => [
        'enabled' => true,
        'request_key' => 'include',
    ],
];
