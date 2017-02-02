<?php

return [
    'home' => [
        'path' => '/',
        'method' => 'GET',
        'action' => 'stubHome',
        'controller' => Faulancer\Fixture\Controller\DummyController::class
    ],
    'stubStatic' => [
        'path' => '/stub',
        'method' => 'GET',
        'action' => 'stubStatic',
        'controller' => Faulancer\Fixture\Controller\DummyController::class
    ],
    'stubDynamic' => [
        'path' => '/stub/(\w+)',
        'method' => 'GET',
        'action' => 'stubDynamic',
        'controller' => Faulancer\Fixture\Controller\DummyController::class
    ]
];