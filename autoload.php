<?php

/*
 * WEBDEVPACK SDK for PHP
 * https://github.com/webdevpack/webdevpack-sdk-php
 * Copyright (c) Amplilabs Ltd.
 * Free to use under the MIT license.
 */

$classes = array(
    'WebDevPack\Client' => 'src/Client.php'
);

spl_autoload_register(function ($class) use ($classes): void {
    if (isset($classes[$class])) {
        require __DIR__ . '/' . $classes[$class];
    }
}, true);
