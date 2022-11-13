<?php

require_once 'plugin.php';

return [
    'apiVersion' => 2,
    'pluginName' => 'simplecontact',
    'pluginClass' => SimplecontactPlugin::class,
    'pluginPath' => __DIR__,
    'config' => [
        'recipient' => 'me@example.com', // your email address
        'template' => null, // aliased path to custom template
    ]
];
