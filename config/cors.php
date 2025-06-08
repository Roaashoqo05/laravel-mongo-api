<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    */

    'paths' => ['api/*', 'storage/*'],  // السماح بالوصول ل API ولملفات الصور

    'allowed_methods' => ['*'],  // السماح بكل طرق HTTP (GET, POST, PUT...)

    'allowed_origins' => ['http://localhost:3000'],  // السماح فقط لل React

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],  // السماح بكل الهيدر

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
